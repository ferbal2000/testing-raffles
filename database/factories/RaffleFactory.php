<?php

namespace Database\Factories;

use App\Enums\RaffleStatus;
use App\Models\Raffle;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Raffle>
 */
class RaffleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status' => RaffleStatus::Draft,
            'starts_at' => null,
            'ends_at' => null,
            'participation_opened_at' => null,
            'participation_closed_at' => null,
            'participation_closed_reason' => null,
            'participation_closed_by_admin_id' => null,
        ];
    }

    public function published(): static
    {
        return $this->afterCreating(function (Raffle $raffle): void {
            $raffle->publish();
        });
    }

    public function closed(): static
    {
        return $this->afterCreating(function (Raffle $raffle): void {
            $raffle->publish();
            $raffle->close(CarbonImmutable::now(), 'raffle_closed', null);
        });
    }

    public function scheduled(?CarbonImmutable $startsAt, ?CarbonImmutable $endsAt): static
    {
        return $this->state(fn () => [
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);
    }

    public function openedForParticipation(?CarbonImmutable $openedAt = null): static
    {
        return $this->state(fn () => [
            'participation_opened_at' => $openedAt ?? CarbonImmutable::now(),
            'participation_closed_at' => null,
            'participation_closed_reason' => null,
            'participation_closed_by_admin_id' => null,
        ]);
    }

    public function participationClosed(?CarbonImmutable $openedAt = null, ?CarbonImmutable $closedAt = null, string $reason = 'admin_closed'): static
    {
        $openedAt ??= CarbonImmutable::now()->subHour();
        $closedAt ??= $openedAt->addHour();

        return $this->state(fn () => [
            'participation_opened_at' => $openedAt,
            'participation_closed_at' => $closedAt,
            'participation_closed_reason' => $reason,
        ]);
    }
}
