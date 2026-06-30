<?php

namespace Database\Factories;

use App\Models\Raffle;
use App\Models\RaffleRegistration;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<RaffleRegistration>
 */
class RaffleRegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'raffle_id' => Raffle::factory(),
            'user_id' => null,
            'name' => fake()->name(),
            'email' => Str::lower(fake()->safeEmail()),
        ];
    }
}
