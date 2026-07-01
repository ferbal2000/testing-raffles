<?php

use App\Models\Raffle;
use App\Models\RaffleRegistration;

function adminRaffleHost(): string
{
    return (string) parse_url((string) config('app.admin_url'), PHP_URL_HOST);
}

function adminRaffleUrl(string $path = '/'): string
{
    return rtrim((string) config('app.admin_url'), '/').$path;
}

function persistedRaffleRegistration(Raffle $raffle, array $attributes = []): RaffleRegistration
{
    return RaffleRegistration::factory()->create(array_merge([
        'raffle_id' => $raffle->id,
    ], $attributes));
}
