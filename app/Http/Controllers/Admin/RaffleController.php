<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Raffle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class RaffleController extends Controller
{
    public function index(): View
    {
        return view('admin.raffles.index', [
            'raffles' => Raffle::query()->latest('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.raffles.create');
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
}
