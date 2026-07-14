<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RaffleRegistrationStatus;
use App\Exceptions\InvalidRaffleRegistrationTransition;
use App\Exceptions\InvalidRaffleTransition;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Raffle;
use App\Models\RaffleRegistration;
use Carbon\CarbonImmutable;
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

    public function registrations(Raffle $raffle): View
    {
        $raffle->load([
            'registrations' => fn ($query) => $query
                ->select(['id', 'raffle_id', 'user_id', 'name', 'email', 'status', 'created_at'])
                ->latest('id'),
        ])->loadCount([
            'registrations',
            'registrations as active_registrations_count' => fn ($query) => $query
                ->where('status', RaffleRegistrationStatus::Active),
            'registrations as flagged_registrations_count' => fn ($query) => $query
                ->where('status', RaffleRegistrationStatus::Flagged),
            'registrations as cancelled_registrations_count' => fn ($query) => $query
                ->where('status', RaffleRegistrationStatus::Cancelled),
        ]);

        return view('admin.raffles.registrations', [
            'raffle' => $raffle,
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
            $raffle->openParticipation(CarbonImmutable::now());
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
            $raffle->publish();
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
        try {
            $raffle->closeParticipation(CarbonImmutable::now(), 'admin_closed', $this->adminActor($request));
        } catch (InvalidRaffleTransition $exception) {
            return redirect()
                ->route('admin.raffles.index')
                ->withErrors(['participation' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.raffles.index')
            ->with('admin.raffles.participation_close_success', trans('admin-raffles.index.flash.participation_close_success'));
    }

    public function flagRegistration(Raffle $raffle, int|string $registration): RedirectResponse
    {
        return $this->transitionRegistration(
            $raffle,
            $registration,
            fn (RaffleRegistration $registration): null => $registration->markForReview(),
            'admin.raffles.registration_status_flag_success',
            trans('admin-raffles.registrations.flash.flag_success'),
        );
    }

    public function cancelRegistration(Raffle $raffle, int|string $registration): RedirectResponse
    {
        return $this->transitionRegistration(
            $raffle,
            $registration,
            fn (RaffleRegistration $registration): null => $registration->cancel(),
            'admin.raffles.registration_status_cancel_success',
            trans('admin-raffles.registrations.flash.cancel_success'),
        );
    }

    public function restoreRegistration(Raffle $raffle, int|string $registration): RedirectResponse
    {
        return $this->transitionRegistration(
            $raffle,
            $registration,
            fn (RaffleRegistration $registration): null => $registration->restoreToActive(),
            'admin.raffles.registration_status_restore_success',
            trans('admin-raffles.registrations.flash.restore_success'),
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
                ->route('admin.raffles.registrations.index', $raffle)
                ->withErrors([
                    'registration_status' => trans('admin-raffles.registrations.errors.status_unavailable'),
                ]);
        }

        return redirect()
            ->route('admin.raffles.registrations.index', $raffle)
            ->with($flashKey, $flashMessage);
    }
}
