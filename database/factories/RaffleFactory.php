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
            $raffle->close();
        });
    }

    public function scheduled(?CarbonImmutable $startsAt, ?CarbonImmutable $endsAt): static
    {
        return $this->state(fn () => [
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);
    }
}
