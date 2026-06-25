<?php

use App\Enums\RaffleStatus;
use App\Exceptions\InvalidRaffleTransition;
use App\Models\Admin;
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

it('accepts participants only when a published raffle has opened participation and has not closed it', function () {
    $eligibleRaffle = Raffle::factory()->published()->openedForParticipation()->create();
    $publishedButUnopenedRaffle = Raffle::factory()->published()->create();

    expect($eligibleRaffle->canAcceptParticipants())->toBeTrue()
        ->and($publishedButUnopenedRaffle->canAcceptParticipants())->toBeFalse();
});

it('does not accept participants for draft, participation-closed, or overall-closed raffles', function () {
    $draftRaffle = Raffle::factory()->openedForParticipation()->create();
    $participationClosedRaffle = Raffle::factory()->published()->participationClosed()->create();
    $closedRaffle = Raffle::factory()->published()->openedForParticipation()->create();
    $closedRaffle->close();

    expect($draftRaffle->canAcceptParticipants())->toBeFalse()
        ->and($participationClosedRaffle->canAcceptParticipants())->toBeFalse()
        ->and($closedRaffle->canAcceptParticipants())->toBeFalse();
});

it('treats starts and ends dates as metadata only for participation eligibility', function () {
    $openedRaffle = Raffle::factory()
        ->published()
        ->openedForParticipation(CarbonImmutable::parse('2026-06-24 10:00:00'))
        ->scheduled(CarbonImmutable::parse('2026-07-01 12:00:00'), CarbonImmutable::parse('2026-07-10 12:00:00'))
        ->create();

    $unopenedRaffle = Raffle::factory()
        ->published()
        ->scheduled(CarbonImmutable::parse('2026-06-01 12:00:00'), CarbonImmutable::parse('2026-06-05 12:00:00'))
        ->create();

    expect($openedRaffle->canAcceptParticipants())->toBeTrue()
        ->and($unopenedRaffle->canAcceptParticipants())->toBeFalse();
});

it('opens participation for a published raffle with the provided timestamp', function () {
    $raffle = Raffle::factory()->published()->create();
    $openedAt = CarbonImmutable::parse('2026-06-25 09:30:00');

    $raffle->openParticipation($openedAt);

    expect($raffle->fresh()->participation_opened_at?->toISOString())->toBe($openedAt->toISOString())
        ->and($raffle->fresh()->canAcceptParticipants())->toBeTrue();
});

it('closes participation for an opened raffle with audit metadata', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->published()->openedForParticipation()->create();
    $closedAt = CarbonImmutable::parse('2026-06-26 15:45:00');

    $raffle->closeParticipation($closedAt, 'admin_closed', $admin);

    $closedRaffle = $raffle->fresh();

    expect($closedRaffle->participation_closed_at?->toISOString())->toBe($closedAt->toISOString())
        ->and($closedRaffle->participation_closed_reason)->toBe('admin_closed')
        ->and($closedRaffle->participation_closed_by_admin_id)->toBe($admin->id)
        ->and($closedRaffle->admin?->is($admin))->toBeTrue()
        ->and($closedRaffle->canAcceptParticipants())->toBeFalse();
});

it('does not open participation for non-published, already-opened, or already-closed raffles', function () {
    $draftRaffle = Raffle::factory()->create();
    $openedRaffle = Raffle::factory()->published()->openedForParticipation()->create();
    $participationClosedRaffle = Raffle::factory()->published()->participationClosed()->create();
    $openedAt = CarbonImmutable::parse('2026-06-25 09:30:00');

    expect(fn () => $draftRaffle->openParticipation($openedAt))->toThrow(InvalidRaffleTransition::class)
        ->and(fn () => $openedRaffle->openParticipation($openedAt))->toThrow(InvalidRaffleTransition::class)
        ->and(fn () => $participationClosedRaffle->openParticipation($openedAt))->toThrow(InvalidRaffleTransition::class);
});

it('does not close participation before it opens or after it is already closed', function () {
    $unopenedRaffle = Raffle::factory()->published()->create();
    $closedRaffle = Raffle::factory()->published()->participationClosed()->create();
    $closedAt = CarbonImmutable::parse('2026-06-26 15:45:00');

    expect(fn () => $unopenedRaffle->closeParticipation($closedAt))->toThrow(InvalidRaffleTransition::class)
        ->and(fn () => $closedRaffle->closeParticipation($closedAt))->toThrow(InvalidRaffleTransition::class);
});
