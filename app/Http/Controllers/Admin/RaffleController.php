<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Raffle;
use Illuminate\View\View;

final class RaffleController extends Controller
{
    public function index(): View
    {
        return view('admin.raffles.index', [
            'raffles' => Raffle::query()->latest('id')->get(),
        ]);
    }
}
