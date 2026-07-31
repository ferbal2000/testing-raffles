<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RaffleRegistrationStatus;
use App\Exceptions\InvalidRaffleRegistrationTransition;
use App\Exceptions\InvalidRaffleTransition;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\RaffleRegistrationSnapshot;
use App\Models\Admin;
use App\Models\Raffle;
use App\Models\RaffleRegistration;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class RaffleController extends Controller
{
    public function index(): View
    {
        return view('admin.raffles.index', [
            'raffles' => Raffle::query()
                ->withCount('registrations')
                ->latest('id')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.raffles.create');
    }

    public function edit(Raffle $raffle): View
    {
        return view('admin.raffles.edit', [
            'raffle' => $raffle,
        ]);
    }

    public function registrations(Request $request, Raffle $raffle): View|JsonResponse|RedirectResponse
    {
        $rawPage = $request->query('page');
        $validPage = is_string($rawPage) && preg_match('/^[1-9][0-9]*$/', $rawPage) === 1;
        $requestedPage = $validPage ? (int) $rawPage : 1;
        $query = $raffle->registrations()
            ->select(['id', 'raffle_id', 'user_id', 'name', 'email', 'status', 'created_at'])
            ->latest('id');
        $registrations = $query->paginate(RaffleRegistrationSnapshot::PER_PAGE, ['*'], 'page', $requestedPage);

        if ($requestedPage > $registrations->lastPage()) {
            $registrations = $query->paginate(RaffleRegistrationSnapshot::PER_PAGE, ['*'], 'page', $registrations->lastPage());
        }

        $raffle->loadCount([
            'registrations',
            'registrations as active_registrations_count' => fn ($query) => $query
                ->where('status', RaffleRegistrationStatus::Active),
            'registrations as flagged_registrations_count' => fn ($query) => $query
                ->where('status', RaffleRegistrationStatus::Flagged),
            'registrations as cancelled_registrations_count' => fn ($query) => $query
                ->where('status', RaffleRegistrationStatus::Cancelled),
        ]);
        $snapshot = RaffleRegistrationSnapshot::make($raffle, $registrations);
        $canonicalQuery = $registrations->currentPage() === 1 ? [] : ['page' => (string) $registrations->currentPage()];

        if ($request->expectsJson()) {
            return response()->json($snapshot);
        }

        if ($request->query() !== $canonicalQuery) {
            return redirect()->to($snapshot['pagination']['canonicalUrl']);
        }

        return view('admin.raffles.registrations', [
            'raffle' => $raffle,
            'snapshot' => $snapshot,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'starts_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'ends_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
        ]);

        Raffle::query()->create([
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
        ]);

        return redirect()
            ->route('admin.raffles.index')
            ->with('admin.raffles.create_success', trans('admin-raffles.create.flash.success'));
    }

    public function update(Request $request, Raffle $raffle): RedirectResponse
    {
        $validated = $request->validate([
            'starts_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'ends_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
        ]);

        $raffle->update([
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
        ]);

        return redirect()
            ->route('admin.raffles.index')
            ->with('admin.raffles.update_success', trans('admin-raffles.edit.flash.success'));
    }

    public function openParticipation(Raffle $raffle): RedirectResponse
    {
        try {
            DB::transaction(function () use ($raffle): void {
                $lockedRaffle = Raffle::query()
                    ->lockForUpdate()
                    ->findOrFail($raffle->getKey());

                $lockedRaffle->openParticipation(CarbonImmutable::now());
            });
        } catch (InvalidRaffleTransition $exception) {
            return redirect()
                ->route('admin.raffles.index')
                ->withErrors(['participation' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.raffles.index')
            ->with('admin.raffles.participation_open_success', trans('admin-raffles.index.flash.participation_open_success'));
    }

    public function publish(Raffle $raffle): RedirectResponse
    {
        try {
            DB::transaction(function () use ($raffle): void {
                $lockedRaffle = Raffle::query()
                    ->lockForUpdate()
                    ->findOrFail($raffle->getKey());

                $lockedRaffle->publish();
            });
        } catch (InvalidRaffleTransition $exception) {
            return redirect()
                ->route('admin.raffles.index')
                ->withErrors(['publish' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.raffles.index')
            ->with('admin.raffles.publish_success', trans('admin-raffles.index.flash.publish_success'));
    }

    public function closeParticipation(Request $request, Raffle $raffle): RedirectResponse
    {
        $admin = $this->adminActor($request);

        try {
            DB::transaction(function () use ($raffle, $admin): void {
                $lockedRaffle = Raffle::query()
                    ->lockForUpdate()
                    ->findOrFail($raffle->getKey());

                $lockedRaffle->closeParticipation(CarbonImmutable::now(), 'admin_closed', $admin);
            });
        } catch (InvalidRaffleTransition $exception) {
            return redirect()
                ->route('admin.raffles.index')
                ->withErrors(['participation' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.raffles.index')
            ->with('admin.raffles.participation_close_success', trans('admin-raffles.index.flash.participation_close_success'));
    }

    public function close(Request $request, Raffle $raffle): RedirectResponse
    {
        $admin = $this->adminActor($request);

        try {
            DB::transaction(function () use ($raffle, $admin): void {
                $lockedRaffle = Raffle::query()
                    ->lockForUpdate()
                    ->findOrFail($raffle->getKey());

                $lockedRaffle->close(CarbonImmutable::now(), 'raffle_closed', $admin);
            });
        } catch (InvalidRaffleTransition) {
            return redirect()
                ->route('admin.raffles.index')
                ->withErrors([
                    'close' => trans('admin-raffles.index.errors.close_unavailable'),
                ]);
        }

        return redirect()
            ->route('admin.raffles.index')
            ->with('admin.raffles.close_success', trans('admin-raffles.index.flash.close_success'));
    }

    public function flagRegistration(Request $request, Raffle $raffle, RaffleRegistration $registration): RedirectResponse
    {
        return $this->transitionRegistration(
            $raffle,
            $registration->id,
            fn (RaffleRegistration $registration): null => $registration->markForReview(),
            'admin.raffles.registration_status_flag_success',
            trans('admin-raffles.registrations.flash.flag_success'),
            $request->input('page'),
        );
    }

    public function cancelRegistration(Request $request, Raffle $raffle, RaffleRegistration $registration): RedirectResponse
    {
        return $this->transitionRegistration(
            $raffle,
            $registration->id,
            fn (RaffleRegistration $registration): null => $registration->cancel(),
            'admin.raffles.registration_status_cancel_success',
            trans('admin-raffles.registrations.flash.cancel_success'),
            $request->input('page'),
        );
    }

    public function restoreRegistration(Request $request, Raffle $raffle, RaffleRegistration $registration): RedirectResponse
    {
        return $this->transitionRegistration(
            $raffle,
            $registration->id,
            fn (RaffleRegistration $registration): null => $registration->restoreToActive(),
            'admin.raffles.registration_status_restore_success',
            trans('admin-raffles.registrations.flash.restore_success'),
            $request->input('page'),
        );
    }

    private function adminActor(Request $request): Admin
    {
        $admin = $request->user('admin');

        abort_unless($admin instanceof Admin, 403);

        return $admin;
    }

    private function transitionRegistration(
        Raffle $raffle,
        int|string $registrationId,
        callable $transition,
        string $flashKey,
        string $flashMessage,
        mixed $page,
    ): RedirectResponse {
        try {
            DB::transaction(function () use ($raffle, $registrationId, $transition): void {
                $registration = $raffle->registrations()
                    ->whereKey($registrationId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $transition($registration);
                $registration->save();
            });
        } catch (InvalidRaffleRegistrationTransition) {
            return redirect()
                ->to($this->registrationIndexUrl($raffle, $page))
                ->withErrors([
                    'registration_status' => trans('admin-raffles.registrations.errors.status_unavailable'),
                ]);
        }

        return redirect()
            ->to($this->registrationIndexUrl($raffle, $page))
            ->with($flashKey, $flashMessage);
    }

    private function registrationIndexUrl(Raffle $raffle, mixed $page): string
    {
        $lastPage = max(1, (int) ceil($raffle->registrations()->count() / RaffleRegistrationSnapshot::PER_PAGE));
        $validPage = (is_int($page) && $page > 0)
            || (is_string($page) && preg_match('/^[1-9][0-9]*$/', $page) === 1);
        $page = $validPage ? min((int) $page, $lastPage) : 1;

        return route('admin.raffles.registrations.index', $page === 1 ? [$raffle] : [$raffle, 'page' => $page]);
    }
}
