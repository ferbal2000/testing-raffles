<?php

namespace App\Http\Controllers\Public;

use App\Enums\RaffleStatus;
use App\Http\Controllers\Controller;
use App\Models\Raffle;
use Illuminate\View\View;

final class RaffleController extends Controller
{
    public function show(int $raffle): View
    {
        $resolvedRaffle = Raffle::query()->publiclyVisible()->findOrFail($raffle);

        return view('public.raffles.show', [
            'raffle' => $resolvedRaffle,
            'statusMessage' => $this->statusMessageFor($resolvedRaffle),
            'availabilityMessage' => $this->availabilityMessageFor($resolvedRaffle),
        ]);
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
}
