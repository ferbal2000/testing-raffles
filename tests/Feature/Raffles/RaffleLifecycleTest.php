<?php

use App\Enums\RaffleStatus;
use App\Exceptions\InvalidRaffleTransition;
use App\Models\Raffle;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function availabilityWindow(string $startsAt, string $endsAt): array
{
    return [CarbonImmutable::parse($startsAt), CarbonImmutable::parse($endsAt)];
}

it('persists a new raffle as draft by default', function () {
    $raffle = Raffle::query()->create([]);

    expect($raffle->status)->toBe(RaffleStatus::Draft)
        ->and($raffle->starts_at)->toBeNull()
        ->and($raffle->ends_at)->toBeNull();
});

it('does not persist a new raffle initially as published', function () {
    $raffle = Raffle::query()->create([
        'status' => RaffleStatus::Published,
    ]);

    expect($raffle->fresh()->status)->toBe(RaffleStatus::Draft);
});

it('does not persist a new raffle initially as closed', function () {
    $raffle = Raffle::query()->create([
        'status' => RaffleStatus::Closed,
    ]);

    expect($raffle->fresh()->status)->toBe(RaffleStatus::Draft);
});

it('persists explicit availability fields on a raffle record', function () {
    [$startsAt, $endsAt] = availabilityWindow('2026-06-20 10:00:00', '2026-06-25 18:00:00');

    $raffle = Raffle::query()->create([
        'starts_at' => $startsAt,
        'ends_at' => $endsAt,
    ]);

    expect($raffle->starts_at?->toISOString())->toBe($startsAt->toISOString())
        ->and($raffle->ends_at?->toISOString())->toBe($endsAt->toISOString());
});

it('rejects unsupported persisted lifecycle states', function () {
    expect(fn () => Raffle::query()->create([
        'status' => 'drawn',
    ]))->toThrow(ValueError::class);
});

it('publishes a persisted draft raffle', function () {
    $raffle = Raffle::factory()->create();

    $raffle->publish();

    expect($raffle->fresh()->status)->toBe(RaffleStatus::Published);
});

it('does not publish a raffle from any state other than draft', function () {
    $raffle = Raffle::factory()->closed()->create();

    expect(fn () => $raffle->publish())->toThrow(InvalidRaffleTransition::class);
});

it('does not publish an unsaved raffle', function () {
    $raffle = Raffle::factory()->make();

    expect(fn () => $raffle->publish())->toThrow(LogicException::class);
});

it('closes a published raffle', function () {
    $raffle = Raffle::factory()->published()->create();

    $raffle->close();

    expect($raffle->fresh()->status)->toBe(RaffleStatus::Closed);
});

it('does not close a draft raffle directly', function () {
    $raffle = Raffle::factory()->create();

    expect(fn () => $raffle->close())->toThrow(InvalidRaffleTransition::class);
});

it('does not auto change lifecycle state from persisted availability dates', function () {
    $publishedRaffle = Raffle::factory()
        ->published()
        ->scheduled(CarbonImmutable::now()->subDays(5), CarbonImmutable::now()->subDay())
        ->create();

    $draftRaffle = Raffle::factory()
        ->scheduled(CarbonImmutable::now()->addDay(), CarbonImmutable::now()->addDays(7))
        ->create();

    expect($publishedRaffle->fresh()->status)->toBe(RaffleStatus::Published)
        ->and($draftRaffle->fresh()->status)->toBe(RaffleStatus::Draft);
});
