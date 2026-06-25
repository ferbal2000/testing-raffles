<?php

use App\Models\Admin;
use App\Models\Raffle;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

function raffleParticipationAdminHost(): string
{
    return (string) parse_url((string) config('app.admin_url'), PHP_URL_HOST);
}

function raffleParticipationAdminUrl(string $path = '/'): string
{
    return rtrim((string) config('app.admin_url'), '/').$path;
}

it('redirects guests to the admin login page for html participation open requests', function () {
    $raffle = Raffle::factory()->published()->create();

    $this->withServerVariables(['HTTP_HOST' => raffleParticipationAdminHost()])
        ->post(raffleParticipationAdminUrl("/raffles/{$raffle->id}/participation/open"))
        ->assertRedirect(route('admin.login'));
});

it('returns 401 for unauthenticated json participation close requests', function () {
    $raffle = Raffle::factory()->published()->openedForParticipation()->create();

    $this->withServerVariables([
        'HTTP_HOST' => raffleParticipationAdminHost(),
        'HTTP_ACCEPT' => 'application/json',
    ])->postJson(raffleParticipationAdminUrl("/raffles/{$raffle->id}/participation/close"))
        ->assertUnauthorized();
});

it('opens participation for a published raffle and redirects with a scoped flash', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()
        ->published()
        ->scheduled(CarbonImmutable::parse('2026-07-01 09:00:00'), CarbonImmutable::parse('2026-07-10 18:00:00'))
        ->create();

    $response = $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => raffleParticipationAdminHost()])
        ->post(raffleParticipationAdminUrl("/raffles/{$raffle->id}/participation/open"));

    $response->assertRedirect(route('admin.raffles.index'))
        ->assertSessionHas('admin.raffles.participation_open_success');

    assertDatabaseHas(Raffle::class, [
        'id' => $raffle->id,
        'status' => 'published',
        'participation_closed_at' => null,
        'participation_closed_reason' => null,
        'participation_closed_by_admin_id' => null,
    ]);

    expect($raffle->fresh()->participation_opened_at)->not->toBeNull()
        ->and($raffle->fresh()->canAcceptParticipants())->toBeTrue();
});

it('closes participation for an opened raffle with admin audit data and a scoped flash', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->published()->openedForParticipation()->create();

    $response = $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => raffleParticipationAdminHost()])
        ->post(raffleParticipationAdminUrl("/raffles/{$raffle->id}/participation/close"));

    $response->assertRedirect(route('admin.raffles.index'))
        ->assertSessionHas('admin.raffles.participation_close_success');

    assertDatabaseHas(Raffle::class, [
        'id' => $raffle->id,
        'participation_closed_reason' => 'admin_closed',
        'participation_closed_by_admin_id' => $admin->id,
    ]);

    expect($raffle->fresh()->participation_closed_at)->not->toBeNull()
        ->and($raffle->fresh()->canAcceptParticipants())->toBeFalse();
});

it('rejects invalid participation open and close transitions without mutating the raffle', function (string $action, Closure $makeRaffle) {
    $admin = Admin::factory()->create();
    $raffle = $makeRaffle();

    $response = $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => raffleParticipationAdminHost()])
        ->from(route('admin.raffles.index'))
        ->post(raffleParticipationAdminUrl("/raffles/{$raffle->id}/participation/{$action}"));

    $response->assertRedirect(route('admin.raffles.index'))
        ->assertSessionHasErrors('participation');

    expect($raffle->fresh()->getAttributes())->toMatchArray($raffle->getAttributes());
})->with(function () {
    return [
        'open draft raffle' => ['open', fn () => Raffle::factory()->create()],
        'close unopened raffle' => ['close', fn () => Raffle::factory()->published()->create()],
        'open participation-closed raffle' => ['open', fn () => Raffle::factory()->published()->participationClosed()->create()],
    ];
});
