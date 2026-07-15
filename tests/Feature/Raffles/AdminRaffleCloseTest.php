<?php

use App\Enums\RaffleStatus;
use App\Http\Controllers\Admin\RaffleController;
use App\Models\Admin;
use App\Models\Raffle;
use App\Models\RaffleRegistration;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\assertDatabaseCount;

uses(RefreshDatabase::class);

function closeRaffleAsAdmin(Raffle $raffle, Admin $admin): Illuminate\Testing\TestResponse
{
    return test()
        ->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->post(adminRaffleUrl("/raffles/{$raffle->id}/close"));
}

function adminCloseBusinessSnapshot(Raffle $raffle): array
{
    return [
        'status' => $raffle->status->value,
        'participation_opened_at' => $raffle->participation_opened_at?->toISOString(),
        'participation_closed_at' => $raffle->participation_closed_at?->toISOString(),
        'participation_closed_reason' => $raffle->participation_closed_reason,
        'participation_closed_by_admin_id' => $raffle->participation_closed_by_admin_id,
    ];
}

it('closes published raffles for every participation state with mutually exclusive translated success', function (
    Closure $makeRaffle,
    bool $expectsNewParticipationAudit,
) {
    $admin = Admin::factory()->create();
    $raffle = $makeRaffle();
    $priorAudit = adminCloseBusinessSnapshot($raffle);
    RaffleRegistration::factory()->count(2)->for($raffle)->create();

    $response = closeRaffleAsAdmin($raffle, $admin);

    $response->assertRedirect(route('admin.raffles.index'))
        ->assertSessionHas('admin.raffles.close_success', trans('admin-raffles.index.flash.close_success'))
        ->assertSessionDoesntHaveErrors('close');

    $closedRaffle = $raffle->fresh();

    expect($closedRaffle->status)->toBe(RaffleStatus::Closed)
        ->and($closedRaffle->registrations()->count())->toBe(2);

    if ($expectsNewParticipationAudit) {
        expect($closedRaffle->participation_closed_at)->not->toBeNull()
            ->and($closedRaffle->participation_closed_reason)->toBe('raffle_closed')
            ->and($closedRaffle->participation_closed_by_admin_id)->toBe($admin->id);
    } else {
        expect($closedRaffle->participation_opened_at?->toISOString())->toBe($priorAudit['participation_opened_at'])
            ->and($closedRaffle->participation_closed_at?->toISOString())->toBe($priorAudit['participation_closed_at'])
            ->and($closedRaffle->participation_closed_reason)->toBe($priorAudit['participation_closed_reason'])
            ->and($closedRaffle->participation_closed_by_admin_id)->toBe($priorAudit['participation_closed_by_admin_id']);
    }
})->with([
    'active participation' => [
        fn () => Raffle::factory()->published()->openedForParticipation()->create(),
        true,
    ],
    'already-closed participation' => [
        fn () => Raffle::factory()->published()->participationClosed()->create(),
        false,
    ],
    'never-opened participation' => [
        fn () => Raffle::factory()->published()->create(),
        false,
    ],
]);

it('rejects ineligible and duplicate close submissions with translated feedback and no mutation', function (
    Closure $makeRaffle,
    bool $submitTwice,
) {
    $admin = Admin::factory()->create();
    $raffle = $makeRaffle();

    if ($submitTwice) {
        closeRaffleAsAdmin($raffle, $admin)
            ->assertSessionHas('admin.raffles.close_success');
        $raffle = $raffle->fresh();
    }

    $before = adminCloseBusinessSnapshot($raffle);

    closeRaffleAsAdmin($raffle, $admin)
        ->assertRedirect(route('admin.raffles.index'))
        ->assertSessionHasErrors([
            'close' => trans('admin-raffles.index.errors.close_unavailable'),
        ])
        ->assertSessionMissing('admin.raffles.close_success');

    expect(adminCloseBusinessSnapshot($raffle->fresh()))->toBe($before);
})->with([
    'draft raffle' => [fn () => Raffle::factory()->create(), false],
    'already-closed raffle' => [fn () => Raffle::factory()->closed()->create(), false],
    'duplicate close submission' => [fn () => Raffle::factory()->published()->create(), true],
]);

it('removes a successfully closed raffle from the public catalog and detail route', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->published()->openedForParticipation()->create();
    $publicHost = (string) parse_url((string) config('app.public_url'), PHP_URL_HOST);
    $publicUrl = rtrim((string) config('app.public_url'), '/');

    $this->withServerVariables(['HTTP_HOST' => $publicHost])
        ->get($publicUrl)
        ->assertOk()
        ->assertSeeText("Sorteo #{$raffle->id}");

    $this->withServerVariables(['HTTP_HOST' => $publicHost])
        ->get("{$publicUrl}/raffles/{$raffle->id}")
        ->assertOk();

    closeRaffleAsAdmin($raffle, $admin)
        ->assertSessionHas('admin.raffles.close_success');

    $this->withServerVariables(['HTTP_HOST' => $publicHost])
        ->get($publicUrl)
        ->assertOk()
        ->assertDontSeeText("Sorteo #{$raffle->id}");

    $this->withServerVariables(['HTTP_HOST' => $publicHost])
        ->get("{$publicUrl}/raffles/{$raffle->id}")
        ->assertNotFound();
});

it('revalidates stale participation models against the current database status', function (string $action, Closure $makeRaffle) {
    $requestingAdmin = Admin::factory()->create();
    $closingAdmin = Admin::factory()->create();
    $staleRaffle = $makeRaffle();
    $staleRaffle->fresh()->close(
        CarbonImmutable::parse('2026-07-15 12:00:00'),
        'raffle_closed',
        $closingAdmin,
    );
    $closedSnapshot = adminCloseBusinessSnapshot($staleRaffle->fresh());
    $request = Request::create('/');
    $request->setUserResolver(fn () => $requestingAdmin);
    $controller = app(RaffleController::class);

    $response = $action === 'open'
        ? $controller->openParticipation($staleRaffle)
        : $controller->closeParticipation($request, $staleRaffle);

    expect($response->getTargetUrl())->toBe(route('admin.raffles.index'))
        ->and(adminCloseBusinessSnapshot($staleRaffle->fresh()))->toBe($closedSnapshot);
})->with([
    'open participation' => ['open', fn () => Raffle::factory()->published()->create()],
    'close participation' => ['close', fn () => Raffle::factory()->published()->openedForParticipation()->create()],
]);

it('locks the current raffle row before each competing admin mutation', function (
    string $path,
    Closure $makeRaffle,
    string $flashKey,
) {
    $admin = Admin::factory()->create();
    $raffle = $makeRaffle();
    $queries = [];

    DB::listen(function ($query) use (&$queries): void {
        $queries[] = strtolower($query->sql);
    });

    $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->post(adminRaffleUrl("/raffles/{$raffle->id}/{$path}"))
        ->assertSessionHas($flashKey);

    $lockQueryIndex = collect($queries)->search(
        fn (string $sql): bool => str_contains($sql, 'from "raffles"')
            && str_contains($sql, 'for update'),
    );
    $updateQueryIndex = collect($queries)->search(
        fn (string $sql): bool => str_contains($sql, 'update "raffles"'),
    );

    expect($lockQueryIndex)->not->toBeFalse()
        ->and($updateQueryIndex)->not->toBeFalse()
        ->and($lockQueryIndex)->toBeLessThan($updateQueryIndex);

    assertDatabaseCount(Raffle::class, 1);
})->with([
    'overall close' => ['close', fn () => Raffle::factory()->published()->openedForParticipation()->create(), 'admin.raffles.close_success'],
    'participation open' => ['participation/open', fn () => Raffle::factory()->published()->create(), 'admin.raffles.participation_open_success'],
    'participation close' => ['participation/close', fn () => Raffle::factory()->published()->openedForParticipation()->create(), 'admin.raffles.participation_close_success'],
]);
