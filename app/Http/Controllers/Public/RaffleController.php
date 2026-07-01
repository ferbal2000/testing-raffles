<?php

namespace App\Http\Controllers\Public;

use App\Enums\RaffleStatus;
use App\Http\Controllers\Controller;
use App\Models\Raffle;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class RaffleController extends Controller
{
    public function index(): View
    {
        $raffles = $this->catalogRaffles();

        return view('public.home', [
            'raffles' => $raffles,
        ]);
    }

    public function show(int $raffle): View
    {
        $resolvedRaffle = $this->resolvePublicRaffle($raffle);

        session()->put($this->publicRaffleSessionKey($resolvedRaffle->getKey()), true);

        return view('public.raffles.show', [
            'raffle' => $resolvedRaffle,
            'statusMessage' => $this->statusMessageFor($resolvedRaffle),
            'availabilityMessage' => $this->availabilityMessageFor($resolvedRaffle),
        ]);
    }

    public function storeParticipation(Request $request, int $raffle): RedirectResponse
    {
        $allowsStaleUnavailableResponse = $request->session()->has($this->publicRaffleSessionKey($raffle));

        $resolvedRaffle = $this->resolveParticipationRaffle($raffle, $allowsStaleUnavailableResponse);

        if ($resolvedRaffle === null) {
            throw (new ModelNotFoundException)->setModel(Raffle::class, [$raffle]);
        }

        if ($this->shouldReturnUnavailableBeforeValidation($resolvedRaffle, $allowsStaleUnavailableResponse)) {
            return $this->redirectToUnavailableDestination($resolvedRaffle);
        }

        $request->merge([
            'email' => $this->normalizeEmail((string) $request->string('email')),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);

        $result = DB::transaction(function () use ($resolvedRaffle, $validated): array {
            $lockedRaffle = Raffle::query()
                ->lockForUpdate()
                ->findOrFail($resolvedRaffle->getKey());

            if ($lockedRaffle->status !== RaffleStatus::Published || ! $lockedRaffle->canAcceptParticipants()) {
                return [
                    'status' => 'unavailable',
                    'raffle' => $lockedRaffle,
                ];
            }

            $registration = $lockedRaffle->registrations()->createOrFirst(
                ['email' => $validated['email']],
                [
                    'user_id' => null,
                    'name' => $validated['name'],
                ],
            );

            return [
                'status' => $registration->wasRecentlyCreated ? 'created' : 'duplicate',
                'raffle' => $lockedRaffle,
            ];
        });

        if ($result['status'] === 'unavailable') {
            return $this->redirectToUnavailableDestination($result['raffle']);
        }

        return redirect()
            ->route('public.raffles.show', $result['raffle'])
            ->with(
                $result['status'] === 'created'
                    ? 'public.raffles.participation_success'
                    : 'public.raffles.participation_duplicate',
                true,
            );
    }

    /**
     * @return Collection<int, Raffle>
     */
    private function catalogRaffles(): Collection
    {
        return Raffle::query()
            ->publiclyVisible()
            ->latest('id')
            ->get()
            ->each(function (Raffle $raffle): void {
                $raffle->setAttribute('status_message', $this->statusMessageFor($raffle));
                $raffle->setAttribute('availability_message', $this->availabilityMessageFor($raffle));
            });
    }

    private function statusMessageFor(Raffle $raffle): string
    {
        return match ($raffle->status) {
            RaffleStatus::Published => __('public-raffles.status.published'),
            default => __('public-raffles.status.unknown'),
        };
    }

    private function availabilityMessageFor(Raffle $raffle): string
    {
        return $raffle->canAcceptParticipants()
            ? __('public-raffles.availability.open')
            : __('public-raffles.availability.closed');
    }

    private function resolvePublicRaffle(int $raffle): Raffle
    {
        return Raffle::query()->publiclyVisible()->findOrFail($raffle);
    }

    private function resolveParticipationRaffle(int $raffle, bool $allowsStaleUnavailableResponse): ?Raffle
    {
        $query = Raffle::query();

        if (! $allowsStaleUnavailableResponse) {
            $query->publiclyVisible();
        }

        return $query->find($raffle);
    }

    private function shouldReturnUnavailableBeforeValidation(Raffle $raffle, bool $allowsStaleUnavailableResponse): bool
    {
        if ($raffle->status === RaffleStatus::Published) {
            return ! $raffle->canAcceptParticipants();
        }

        return $allowsStaleUnavailableResponse;
    }

    private function redirectToUnavailableDestination(Raffle $raffle): RedirectResponse
    {
        return redirect()
            ->route(
                $raffle->status === RaffleStatus::Published
                    ? 'public.raffles.show'
                    : 'public.home',
                $raffle->status === RaffleStatus::Published ? $raffle : [],
            )
            ->with('public.raffles.participation_unavailable', true);
    }

    private function publicRaffleSessionKey(int $raffle): string
    {
        return "public.raffles.viewed.{$raffle}";
    }

    private function normalizeEmail(string $email): string
    {
        return Str::lower(trim($email));
    }
}
