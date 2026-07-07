<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InvalidRaffleTransition;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Raffle;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
                ->select(['id', 'raffle_id', 'user_id', 'name', 'email', 'created_at'])
                ->latest('id'),
        ])->loadCount('registrations');

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

    private function adminActor(Request $request): Admin
    {
        $admin = $request->user('admin');

        abort_unless($admin instanceof Admin, 403);

        return $admin;
    }
}
