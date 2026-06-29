<?php

namespace App\Http\Controllers\Public;

use App\Enums\RaffleStatus;
use App\Http\Controllers\Controller;
use App\Models\Raffle;
use Illuminate\Support\Collection;
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
        $resolvedRaffle = Raffle::query()->publiclyVisible()->findOrFail($raffle);

        return view('public.raffles.show', [
            'raffle' => $resolvedRaffle,
            'statusMessage' => $this->statusMessageFor($resolvedRaffle),
            'availabilityMessage' => $this->availabilityMessageFor($resolvedRaffle),
        ]);
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
}
