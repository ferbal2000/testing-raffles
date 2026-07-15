<?php

use App\Enums\RaffleStatus;
use App\Exceptions\InvalidRaffleTransition;
use App\Models\Admin;
use App\Models\Raffle;
use App\Models\RaffleRegistration;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function availabilityWindow(string $startsAt, string $endsAt): array
{
    return [CarbonImmutable::parse($startsAt), CarbonImmutable::parse($endsAt)];
}

function raffleClosureSnapshot(Raffle $raffle): array
{
    return [
        'status' => $raffle->status->value,
        'participation_opened_at' => $raffle->participation_opened_at?->toISOString(),
        'participation_closed_at' => $raffle->participation_closed_at?->toISOString(),
        'participation_closed_reason' => $raffle->participation_closed_reason,
        'participation_closed_by_admin_id' => $raffle->participation_closed_by_admin_id,
    ];
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

it('allows publication checks only for draft raffles', function () {
    $draftRaffle = Raffle::factory()->create();
    $publishedRaffle = Raffle::factory()->published()->create();
    $closedRaffle = Raffle::factory()->closed()->create();

    expect($draftRaffle->canPublish())->toBeTrue()
        ->and($publishedRaffle->canPublish())->toBeFalse()
        ->and($closedRaffle->canPublish())->toBeFalse();
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

    $raffle->close(CarbonImmutable::now(), 'raffle_closed', null);

    expect($raffle->fresh()->status)->toBe(RaffleStatus::Closed);
});

it('does not close a draft raffle directly', function () {
    $raffle = Raffle::factory()->create();

    expect(fn () => $raffle->close(CarbonImmutable::now(), 'raffle_closed', null))
        ->toThrow(InvalidRaffleTransition::class);
});

it('allows overall closure only for published raffles regardless of participation state', function () {
    $activeRaffle = Raffle::factory()->published()->openedForParticipation()->create();
    $participationClosedRaffle = Raffle::factory()->published()->participationClosed()->create();
    $neverOpenedRaffle = Raffle::factory()->published()->create();
    $draftRaffle = Raffle::factory()->create();
    $closedRaffle = Raffle::factory()->closed()->create();

    expect($activeRaffle->canClose())->toBeTrue()
        ->and($participationClosedRaffle->canClose())->toBeTrue()
        ->and($neverOpenedRaffle->canClose())->toBeTrue()
        ->and($draftRaffle->canClose())->toBeFalse()
        ->and($closedRaffle->canClose())->toBeFalse();
});

it('closes a published raffle and active participation in one model save without changing registrations', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->published()->openedForParticipation()->create();
    RaffleRegistration::factory()->count(2)->for($raffle)->create();
    $closedAt = CarbonImmutable::parse('2026-07-15 14:30:00');
    $savingCount = 0;
    $countSaves = true;

    Raffle::saving(function (Raffle $savingRaffle) use ($raffle, &$savingCount, &$countSaves): void {
        if ($countSaves && $savingRaffle->is($raffle)) {
            $savingCount++;
        }
    });

    $raffle->close($closedAt, 'raffle_closed', $admin);
    $countSaves = false;

    $closedRaffle = $raffle->fresh();

    expect($closedRaffle->status)->toBe(RaffleStatus::Closed)
        ->and($closedRaffle->participation_closed_at?->toISOString())->toBe($closedAt->toISOString())
        ->and($closedRaffle->participation_closed_reason)->toBe('raffle_closed')
        ->and($closedRaffle->participation_closed_by_admin_id)->toBe($admin->id)
        ->and($closedRaffle->registrations()->count())->toBe(2)
        ->and($savingCount)->toBe(1)
        ->and(array_key_exists('ready_to_draw', $closedRaffle->getAttributes()))->toBeFalse()
        ->and(array_key_exists('drawn', $closedRaffle->getAttributes()))->toBeFalse();
});

it('preserves prior participation closure audit when closing the overall raffle', function () {
    $priorAdmin = Admin::factory()->create();
    $closingAdmin = Admin::factory()->create();
    $openedAt = CarbonImmutable::parse('2026-07-10 09:00:00');
    $participationClosedAt = CarbonImmutable::parse('2026-07-12 18:00:00');
    $raffle = Raffle::factory()
        ->published()
        ->participationClosed($openedAt, $participationClosedAt, 'admin_closed')
        ->create([
            'participation_closed_by_admin_id' => $priorAdmin->id,
        ]);

    $raffle->close(CarbonImmutable::parse('2026-07-15 14:30:00'), 'raffle_closed', $closingAdmin);

    $closedRaffle = $raffle->fresh();

    expect($closedRaffle->status)->toBe(RaffleStatus::Closed)
        ->and($closedRaffle->participation_opened_at?->toISOString())->toBe($openedAt->toISOString())
        ->and($closedRaffle->participation_closed_at?->toISOString())->toBe($participationClosedAt->toISOString())
        ->and($closedRaffle->participation_closed_reason)->toBe('admin_closed')
        ->and($closedRaffle->participation_closed_by_admin_id)->toBe($priorAdmin->id);
});

it('keeps never-opened participation audit null when closing the overall raffle', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->published()->create();

    $raffle->close(CarbonImmutable::parse('2026-07-15 14:30:00'), 'raffle_closed', $admin);

    $closedRaffle = $raffle->fresh();

    expect($closedRaffle->status)->toBe(RaffleStatus::Closed)
        ->and($closedRaffle->participation_opened_at)->toBeNull()
        ->and($closedRaffle->participation_closed_at)->toBeNull()
        ->and($closedRaffle->participation_closed_reason)->toBeNull()
        ->and($closedRaffle->participation_closed_by_admin_id)->toBeNull();
});

it('rejects draft and already-closed overall transitions without mutating business data', function (Closure $makeRaffle) {
    $admin = Admin::factory()->create();
    $raffle = $makeRaffle();
    $before = raffleClosureSnapshot($raffle);

    expect(fn () => $raffle->close(CarbonImmutable::now(), 'raffle_closed', $admin))
        ->toThrow(InvalidRaffleTransition::class)
        ->and(raffleClosureSnapshot($raffle->fresh()))->toBe($before);
})->with([
    'draft' => fn () => Raffle::factory()->create(),
    'closed' => fn () => Raffle::factory()->closed()->create(),
]);

it('persists no overall or participation closure fields when the single save fails', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->published()->openedForParticipation()->create();
    $before = raffleClosureSnapshot($raffle);
    $failSave = true;

    Raffle::saving(function (Raffle $savingRaffle) use ($raffle, &$failSave): void {
        if ($failSave && $savingRaffle->is($raffle) && $savingRaffle->status === RaffleStatus::Closed) {
            throw new \RuntimeException('Injected raffle save failure.');
        }
    });

    expect(fn () => $raffle->close(CarbonImmutable::now(), 'raffle_closed', $admin))
        ->toThrow(\RuntimeException::class, 'Injected raffle save failure.');

    $failSave = false;

    expect(raffleClosureSnapshot($raffle->fresh()))->toBe($before);
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

    expect($eligibleRaffle->fresh()->status)->toBe(RaffleStatus::Published)
        ->and($eligibleRaffle->canAcceptParticipants())->toBeTrue()
        ->and($publishedButUnopenedRaffle->fresh()->status)->toBe(RaffleStatus::Published)
        ->and($publishedButUnopenedRaffle->canAcceptParticipants())->toBeFalse();
});

it('does not accept participants for draft, participation-closed, or overall-closed raffles', function () {
    $draftRaffle = Raffle::factory()->openedForParticipation()->create();
    $participationClosedRaffle = Raffle::factory()->published()->participationClosed()->create();
    $closedRaffle = Raffle::factory()->published()->openedForParticipation()->create();
    $closedRaffle->close(CarbonImmutable::now(), 'raffle_closed', null);

    expect($draftRaffle->fresh()->status)->toBe(RaffleStatus::Draft)
        ->and($draftRaffle->canAcceptParticipants())->toBeFalse()
        ->and($participationClosedRaffle->fresh()->status)->toBe(RaffleStatus::Published)
        ->and($participationClosedRaffle->canAcceptParticipants())->toBeFalse()
        ->and($closedRaffle->fresh()->status)->toBe(RaffleStatus::Closed)
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

it('returns only published raffles from the public visibility scope', function () {
    $publishedRaffle = Raffle::factory()->published()->create();
    $draftRaffle = Raffle::factory()->create();
    $closedRaffle = Raffle::factory()->closed()->create();

    $visibleRaffleIds = Raffle::query()
        ->publiclyVisible()
        ->pluck('id')
        ->all();

    expect($visibleRaffleIds)->toBe([$publishedRaffle->id])
        ->and($visibleRaffleIds)->not->toContain($draftRaffle->id)
        ->and($visibleRaffleIds)->not->toContain($closedRaffle->id);
});

it('does not resolve draft or closed raffles through the public visibility scope', function () {
    $draftRaffle = Raffle::factory()->create();
    $closedRaffle = Raffle::factory()->closed()->create();

    expect(Raffle::query()->publiclyVisible()->find($draftRaffle->id))->toBeNull()
        ->and(Raffle::query()->publiclyVisible()->find($closedRaffle->id))->toBeNull();
});
