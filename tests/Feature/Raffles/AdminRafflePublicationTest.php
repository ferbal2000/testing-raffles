<?php

use App\Enums\RaffleStatus;
use App\Http\Controllers\Admin\RaffleController;
use App\Models\Admin;
use App\Models\Raffle;
use App\Models\RaffleRegistration;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function publishRaffle(Raffle $raffle, ?Admin $admin = null): Illuminate\Testing\TestResponse
{
    $request = test();

    if ($admin !== null) {
        $request = $request->actingAs($admin, 'admin');
    }

    return $request
        ->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->post(adminRaffleUrl("/raffles/{$raffle->id}/publish"));
}

function publicationPublicRaffleHost(): string
{
    return (string) parse_url((string) config('app.public_url'), PHP_URL_HOST);
}

function publicationPublicRaffleUrl(string $path = ''): string
{
    return rtrim((string) config('app.public_url'), '/').$path;
}

function publicationBusinessSnapshot(Raffle $raffle): array
{
    return [
        'status' => $raffle->status->value,
        'starts_at' => $raffle->starts_at?->toISOString(),
        'ends_at' => $raffle->ends_at?->toISOString(),
        'participation_opened_at' => $raffle->participation_opened_at?->toISOString(),
        'participation_closed_at' => $raffle->participation_closed_at?->toISOString(),
        'participation_closed_reason' => $raffle->participation_closed_reason,
        'participation_closed_by_admin_id' => $raffle->participation_closed_by_admin_id,
        'registrations_count' => $raffle->registrations()->count(),
    ];
}

it('publishes a draft raffle for an authenticated admin', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create();

    publishRaffle($raffle, $admin)
        ->assertRedirect(route('admin.raffles.index'))
        ->assertSessionHas('admin.raffles.publish_success', 'El sorteo se publicó.');

    expect($raffle->fresh()->status)->toBe(RaffleStatus::Published);
});

it('rejects unauthenticated publish submissions through existing admin authentication', function () {
    $raffle = Raffle::factory()->create();

    publishRaffle($raffle)
        ->assertRedirect(route('admin.login'));

    expect($raffle->fresh()->status)->toBe(RaffleStatus::Draft);
});

it('rejects stale non-draft publish submissions without changing the raffle status', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->published()->create();

    publishRaffle($raffle, $admin)
        ->assertRedirect(route('admin.raffles.index'))
        ->assertSessionHasErrors(['publish' => 'Cannot transition raffle from [published] to [published].']);

    expect($raffle->fresh()->status)->toBe(RaffleStatus::Published);
});

it('rejects a stale draft-bound publish after committed closure without changing business data', function () {
    $requestingAdmin = Admin::factory()->create();
    $closingAdmin = Admin::factory()->create();
    $staleRaffle = Raffle::factory()->scheduled(
        CarbonImmutable::parse('2026-07-01 10:00:00'),
        CarbonImmutable::parse('2026-07-10 18:00:00'),
    )->create();
    $committedRaffle = $staleRaffle->fresh();
    $committedRaffle->publish();
    $committedRaffle->openParticipation(CarbonImmutable::parse('2026-07-02 10:00:00'));
    RaffleRegistration::factory()->count(2)->for($committedRaffle)->create();
    $committedRaffle->close(
        CarbonImmutable::parse('2026-07-03 10:00:00'),
        'raffle_closed',
        $closingAdmin,
    );
    $closedSnapshot = publicationBusinessSnapshot($committedRaffle->fresh());

    $this->actingAs($requestingAdmin, 'admin');
    $response = app(RaffleController::class)->publish($staleRaffle);

    expect($response->getTargetUrl())->toBe(route('admin.raffles.index'))
        ->and(publicationBusinessSnapshot($staleRaffle->fresh()))->toBe($closedSnapshot)
        ->and(session()->has('admin.raffles.publish_success'))->toBeFalse()
        ->and(session('errors')->get('publish'))->toBe([
            'Cannot transition raffle from [closed] to [published].',
        ]);

    $this->withServerVariables(['HTTP_HOST' => publicationPublicRaffleHost()])
        ->get(publicationPublicRaffleUrl("/raffles/{$staleRaffle->id}"))
        ->assertNotFound();
});

it('makes a successfully published raffle publicly resolvable', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create();

    $this->withServerVariables(['HTTP_HOST' => publicationPublicRaffleHost()])
        ->get(publicationPublicRaffleUrl("/raffles/{$raffle->id}"))
        ->assertNotFound();

    publishRaffle($raffle, $admin)
        ->assertRedirect(route('admin.raffles.index'));

    $this->withServerVariables(['HTTP_HOST' => publicationPublicRaffleHost()])
        ->get(publicationPublicRaffleUrl("/raffles/{$raffle->id}"))
        ->assertOk();
});

it('does not change participation timestamps when publishing a raffle', function () {
    $admin = Admin::factory()->create();
    $startsAt = CarbonImmutable::parse('2026-07-01 10:00:00');
    $endsAt = CarbonImmutable::parse('2026-07-10 18:00:00');
    $raffle = Raffle::factory()->scheduled($startsAt, $endsAt)->create();

    publishRaffle($raffle, $admin)
        ->assertRedirect(route('admin.raffles.index'));

    $publishedRaffle = $raffle->fresh();

    expect($publishedRaffle->status)->toBe(RaffleStatus::Published)
        ->and($publishedRaffle->participation_opened_at)->toBeNull()
        ->and($publishedRaffle->participation_closed_at)->toBeNull()
        ->and($publishedRaffle->starts_at?->toISOString())->toBe($startsAt->toISOString())
        ->and($publishedRaffle->ends_at?->toISOString())->toBe($endsAt->toISOString());
});
