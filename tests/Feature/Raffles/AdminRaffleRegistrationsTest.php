<?php

use App\Enums\RaffleRegistrationStatus;
use App\Http\Resources\Admin\RaffleRegistrationSnapshot;
use App\Models\Admin;
use App\Models\Raffle;
use App\Models\RaffleRegistration;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

it('redirects guests to the admin login page for html raffle registration list requests', function () {
    $raffle = Raffle::factory()->create();

    $this->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->get(adminRaffleUrl("/raffles/{$raffle->id}/registrations"))
        ->assertRedirect(route('admin.login'));
});

it('returns 401 for unauthenticated json raffle registration list requests', function () {
    $raffle = Raffle::factory()->create();

    $this->withServerVariables([
        'HTTP_HOST' => adminRaffleHost(),
        'HTTP_ACCEPT' => 'application/json',
    ])->getJson(adminRaffleUrl("/raffles/{$raffle->id}/registrations"))
        ->assertUnauthorized();
});

it('shows an explicit empty state for authenticated admins when a raffle has no registrations', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create();

    $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->get(adminRaffleUrl("/raffles/{$raffle->id}/registrations"))
        ->assertOk()
        ->assertSeeText("Inscripciones del sorteo #{$raffle->id}")
        ->assertSee(route('admin.raffles.index'), escape: false)
        ->assertSeeText('Volver al listado')
        ->assertSeeText('Todavía no hay inscripciones para este sorteo.')
        ->assertDontSeeText('Exportar')
        ->assertDontSeeText('Abrir participación')
        ->assertDontSeeText('Cerrar participación');
});

it('shows a read-only zero-registration summary while preserving the empty state', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create();

    $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->get(adminRaffleUrl("/raffles/{$raffle->id}/registrations"))
        ->assertOk()
        ->assertSeeText('Resumen de inscripciones')
        ->assertSeeText('0 inscripciones registradas')
        ->assertSeeText('0 activas')
        ->assertSeeText('0 para revisión')
        ->assertSeeText('0 canceladas')
        ->assertSeeText('Todavía no hay inscripciones para este sorteo.')
        ->assertDontSeeText('Ticket')
        ->assertDontSeeText('Capacidad')
        ->assertDontSeeText('Pago')
        ->assertDontSeeText('Sorteo garantizado')
        ->assertDontSeeText('Exportar')
        ->assertDontSeeText('Eliminar');
});

it('shows existing registrations newest-first with allowed fields and read-only linked-account signals', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create();
    $linkedUser = User::factory()->create();

    $olderRegistration = persistedRaffleRegistration($raffle, [
        'user_id' => null,
        'name' => 'Older Guest',
        'email' => 'OLDER@example.com',
    ]);
    $olderRegistration->forceFill([
        'created_at' => CarbonImmutable::parse('2026-07-01 09:15:00'),
    ])->save();

    $newerRegistration = persistedRaffleRegistration($raffle, [
        'user_id' => $linkedUser->id,
        'name' => 'Newer Guest',
        'email' => 'NEWER@example.com',
    ]);
    $newerRegistration->forceFill([
        'created_at' => CarbonImmutable::parse('2026-07-02 11:45:00'),
    ])->save();

    $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->get(adminRaffleUrl("/raffles/{$raffle->id}/registrations"))
        ->assertOk()
        ->assertSee(route('admin.raffles.index'), escape: false)
        ->assertSeeText('Volver al listado')
        ->assertSeeInOrder([
            'Newer Guest',
            'newer@example.com',
            'Activa',
            '2026-07-02 11:45',
            'Cuenta vinculada',
            'Older Guest',
            'older@example.com',
            'Activa',
            '2026-07-01 09:15',
            'Sin cuenta vinculada',
        ], escape: false)
        ->assertDontSeeText('Todavía no hay inscripciones para este sorteo.')
        ->assertDontSeeText('Ticket')
        ->assertDontSeeText('Pago')
        ->assertDontSeeText('Ganador')
        ->assertDontSeeText('Aprobar')
        ->assertDontSeeText('Rechazar')
        ->assertSeeText('Cancelar inscripción')
        ->assertSeeText('Marcar para revisión')
        ->assertDontSeeText('Exportar')
        ->assertDontSeeText('Eliminar')
        ->assertDontSeeText('Editar')
        ->assertDontSeeText('Abrir participación')
        ->assertDontSeeText('Cerrar participación');
});

it('shows a read-only non-zero summary while preserving newest-first registrations', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create();

    $olderRegistration = persistedRaffleRegistration($raffle, [
        'name' => 'Older Summary Guest',
        'email' => 'older-summary@example.com',
    ]);
    $olderRegistration->forceFill([
        'created_at' => CarbonImmutable::parse('2026-07-01 09:15:00'),
    ])->save();

    $newerRegistration = persistedRaffleRegistration($raffle, [
        'name' => 'Newer Summary Guest',
        'email' => 'newer-summary@example.com',
    ]);
    $newerRegistration->forceFill([
        'created_at' => CarbonImmutable::parse('2026-07-02 11:45:00'),
    ])->save();

    $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->get(adminRaffleUrl("/raffles/{$raffle->id}/registrations"))
        ->assertOk()
        ->assertSeeText('Resumen de inscripciones')
        ->assertSeeText('2 inscripciones registradas')
        ->assertSeeInOrder([
            'Newer Summary Guest',
            'newer-summary@example.com',
            'Older Summary Guest',
            'older-summary@example.com',
        ], escape: false)
        ->assertDontSeeText('Todavía no hay inscripciones para este sorteo.')
        ->assertDontSeeText('Ticket')
        ->assertDontSeeText('Capacidad')
        ->assertDontSeeText('Pago')
        ->assertDontSeeText('Sorteo garantizado')
        ->assertDontSeeText('Exportar')
        ->assertDontSeeText('Eliminar');
});

it('shows status-specific actions, separated totals, and registrations newest-first', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create();

    $registrations = [];
    foreach ([
        'active' => ['Active Guest', 'active@example.com', RaffleRegistrationStatus::Active, '2026-07-01 09:15:00'],
        'flagged' => ['Flagged Guest', 'flagged@example.com', RaffleRegistrationStatus::Flagged, '2026-07-02 11:45:00'],
        'cancelled' => ['Cancelled Guest', 'cancelled@example.com', RaffleRegistrationStatus::Cancelled, '2026-07-03 17:30:00'],
    ] as $key => [$name, $email, $status, $createdAt]) {
        $registrations[$key] = persistedRaffleRegistration($raffle, compact('name', 'email', 'status'));
        $registrations[$key]->forceFill(['created_at' => CarbonImmutable::parse($createdAt)])->save();
    }

    $otherRaffle = Raffle::factory()->create();
    persistedRaffleRegistration($otherRaffle, [
        'status' => RaffleRegistrationStatus::Active,
    ]);

    $restoreUrl = route('admin.raffles.registrations.restore', [$raffle, $registrations['flagged']]);

    $response = $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->get(adminRaffleUrl("/raffles/{$raffle->id}/registrations"));

    $response->assertOk()
        ->assertSeeText('Activas')
        ->assertSeeText('1 activa')
        ->assertSeeText('Para revisión')
        ->assertSeeText('1 para revisión')
        ->assertSeeText('Canceladas')
        ->assertSeeText('1 cancelada')
        ->assertSeeText('Total registradas')
        ->assertSeeText('3 inscripciones registradas')
        ->assertSeeText('Estado')
        ->assertSeeText('Acciones')
        ->assertSeeInOrder([
            'Cancelled Guest',
            'cancelled@example.com',
            'Cancelada',
            'Sin acciones disponibles',
            'Flagged Guest',
            'flagged@example.com',
            'Para revisión',
            'Quitar de revisión',
            'Active Guest',
            'active@example.com',
            'Activa',
            'Marcar para revisión',
            'Cancelar inscripción',
        ], escape: false)
        ->assertSee(route('admin.raffles.registrations.flag', [$raffle, $registrations['active']]), escape: false)
        ->assertSee(route('admin.raffles.registrations.cancel', [$raffle, $registrations['active']]), escape: false)
        ->assertDontSee(route('admin.raffles.registrations.restore', [$raffle, $registrations['active']]), escape: false)
        ->assertDontSee(route('admin.raffles.registrations.flag', [$raffle, $registrations['flagged']]), escape: false)
        ->assertDontSee(route('admin.raffles.registrations.cancel', [$raffle, $registrations['flagged']]), escape: false)
        ->assertSee($restoreUrl, escape: false)
        ->assertDontSee(route('admin.raffles.registrations.flag', [$raffle, $registrations['cancelled']]), escape: false)
        ->assertDontSee(route('admin.raffles.registrations.cancel', [$raffle, $registrations['cancelled']]), escape: false)
        ->assertDontSee(route('admin.raffles.registrations.restore', [$raffle, $registrations['cancelled']]), escape: false)
        ->assertSee('¿Quitar esta inscripción de revisión y restaurarla a activa?')
        ->assertSee('<input type="hidden" name="_token" value="'.csrf_token().'" autocomplete="off">', escape: false)
        ->assertDontSeeText('Aprobar')
        ->assertDontSeeText('Rechazar')
        ->assertDontSeeText('Reactivar')
        ->assertDontSeeText('Ticket')
        ->assertDontSeeText('Pago');

    expect(substr_count($response->getContent(), $restoreUrl))->toBe(1);
});

it('shows scoped review-cleared success feedback on the registrations page', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create();

    $this->actingAs($admin, 'admin')
        ->withSession([
            'admin.raffles.registration_status_restore_success' => 'La inscripción se quitó de revisión y se restauró a activa.',
        ])
        ->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->get(adminRaffleUrl("/raffles/{$raffle->id}/registrations"))
        ->assertOk()
        ->assertSeeText('La inscripción se quitó de revisión y se restauró a activa.');
});

it('renders every status action boundary and restores a flagged row through the admin http flow', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create();
    $activeRegistration = persistedRaffleRegistration($raffle, [
        'name' => 'Active Runtime Guest',
        'status' => RaffleRegistrationStatus::Active,
    ]);
    $flaggedRegistration = persistedRaffleRegistration($raffle, [
        'name' => 'Flagged Runtime Guest',
        'status' => RaffleRegistrationStatus::Flagged,
    ]);
    $cancelledRegistration = persistedRaffleRegistration($raffle, [
        'name' => 'Cancelled Runtime Guest',
        'status' => RaffleRegistrationStatus::Cancelled,
    ]);
    $indexUrl = adminRaffleUrl("/raffles/{$raffle->id}/registrations");
    $restoreUrl = route('admin.raffles.registrations.restore', [$raffle, $flaggedRegistration]);

    $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->get($indexUrl)
        ->assertOk()
        ->assertSee(route('admin.raffles.registrations.flag', [$raffle, $activeRegistration]), escape: false)
        ->assertSee(route('admin.raffles.registrations.cancel', [$raffle, $activeRegistration]), escape: false)
        ->assertSee($restoreUrl, escape: false)
        ->assertDontSee(route('admin.raffles.registrations.restore', [$raffle, $cancelledRegistration]), escape: false);

    $this->actingAs($admin, 'admin')
        ->followingRedirects()
        ->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->post($restoreUrl)
        ->assertOk()
        ->assertSeeText('La inscripción se quitó de revisión y se restauró a activa.')
        ->assertSeeText('2 activas')
        ->assertSeeText('0 para revisión')
        ->assertSee(route('admin.raffles.registrations.flag', [$raffle, $flaggedRegistration]), escape: false)
        ->assertSee(route('admin.raffles.registrations.cancel', [$raffle, $flaggedRegistration]), escape: false)
        ->assertDontSee($restoreUrl, escape: false);

    expect($flaggedRegistration->fresh()->status)->toBe(RaffleRegistrationStatus::Active)
        ->and($cancelledRegistration->fresh()->status)->toBe(RaffleRegistrationStatus::Cancelled);
});

it('preserves public registration eligibility and creates active registrations', function () {
    $raffle = Raffle::factory()
        ->published()
        ->openedForParticipation(CarbonImmutable::parse('2026-07-01 09:00:00'))
        ->create();
    $publicHost = (string) parse_url((string) config('app.public_url'), PHP_URL_HOST);
    $publicUrl = rtrim((string) config('app.public_url'), '/');

    $this->followingRedirects()
        ->withServerVariables(['HTTP_HOST' => $publicHost])
        ->post("{$publicUrl}/raffles/{$raffle->id}/participation", [
            'name' => 'Public Regression Guest',
            'email' => ' PUBLIC-REGRESSION@Example.COM ',
        ])
        ->assertOk()
        ->assertSeeText('Tu participación quedó registrada.');

    assertDatabaseHas(RaffleRegistration::class, [
        'raffle_id' => $raffle->id,
        'name' => 'Public Regression Guest',
        'email' => 'public-regression@example.com',
        'status' => RaffleRegistrationStatus::Active->value,
    ]);
    assertDatabaseCount(RaffleRegistration::class, 1);
});

it('flags and cancels active registrations with scoped success feedback', function (string $action, RaffleRegistrationStatus $expectedStatus, string $flashKey, string $feedback) {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create();
    $registration = persistedRaffleRegistration($raffle, [
        'status' => RaffleRegistrationStatus::Active,
    ]);

    $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->post(route("admin.raffles.registrations.{$action}", [$raffle, $registration]))
        ->assertRedirect(route('admin.raffles.registrations.index', $raffle))
        ->assertSessionHas($flashKey, $feedback)
        ->assertSessionHasNoErrors();

    assertDatabaseHas(RaffleRegistration::class, [
        'id' => $registration->id,
        'raffle_id' => $raffle->id,
        'status' => $expectedStatus->value,
    ]);
})->with([
    'flag' => ['flag', RaffleRegistrationStatus::Flagged, 'admin.raffles.registration_status_flag_success', 'La inscripción se marcó para revisión.'],
    'cancel' => ['cancel', RaffleRegistrationStatus::Cancelled, 'admin.raffles.registration_status_cancel_success', 'La inscripción se canceló.'],
]);

it('rejects terminal registration status actions with unchanged status and scoped errors', function (RaffleRegistrationStatus $initialStatus, string $action) {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create();
    $registration = persistedRaffleRegistration($raffle, [
        'status' => $initialStatus,
    ]);

    $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->post(route("admin.raffles.registrations.{$action}", [$raffle, $registration]))
        ->assertRedirect(route('admin.raffles.registrations.index', $raffle))
        ->assertSessionHasErrors(['registration_status' => 'Esta acción ya no está disponible para esta inscripción.']);

    assertDatabaseHas(RaffleRegistration::class, [
        'id' => $registration->id,
        'raffle_id' => $raffle->id,
        'status' => $initialStatus->value,
    ]);
})->with([
    'flag flagged' => [RaffleRegistrationStatus::Flagged, 'flag'],
    'cancel flagged' => [RaffleRegistrationStatus::Flagged, 'cancel'],
    'flag cancelled' => [RaffleRegistrationStatus::Cancelled, 'flag'],
    'cancel cancelled' => [RaffleRegistrationStatus::Cancelled, 'cancel'],
]);

it('does not mutate a registration through another raffles nested status action route', function (string $action) {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create();
    $otherRaffle = Raffle::factory()->create();
    $registration = persistedRaffleRegistration($otherRaffle, [
        'status' => RaffleRegistrationStatus::Active,
    ]);

    $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->post(route("admin.raffles.registrations.{$action}", [$raffle, $registration]))
        ->assertNotFound();

    assertDatabaseHas(RaffleRegistration::class, [
        'id' => $registration->id,
        'raffle_id' => $otherRaffle->id,
        'status' => RaffleRegistrationStatus::Active->value,
    ]);
})->with([
    'flag' => ['flag'],
    'cancel' => ['cancel'],
]);

it('redirects guests to the admin login page for html registration status action requests', function (string $action) {
    $raffle = Raffle::factory()->create();
    $registration = persistedRaffleRegistration($raffle);

    $this->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->post(route("admin.raffles.registrations.{$action}", [$raffle, $registration]))
        ->assertRedirect(route('admin.login'));
})->with([
    'flag' => ['flag'],
    'cancel' => ['cancel'],
]);

it('returns 401 for unauthenticated json registration status action requests', function (string $action) {
    $raffle = Raffle::factory()->create();
    $registration = persistedRaffleRegistration($raffle);

    $this->withServerVariables([
        'HTTP_HOST' => adminRaffleHost(),
        'HTTP_ACCEPT' => 'application/json',
    ])->postJson(route("admin.raffles.registrations.{$action}", [$raffle, $registration]))
        ->assertUnauthorized();
})->with([
    'flag' => ['flag'],
    'cancel' => ['cancel'],
]);

it('allows restore only for flagged registrations', function (RaffleRegistrationStatus $status, bool $expected) {
    $registration = new RaffleRegistration(['status' => $status]);

    expect($registration->canBeRestored())->toBe($expected);
})->with([
    'active' => [RaffleRegistrationStatus::Active, false],
    'flagged' => [RaffleRegistrationStatus::Flagged, true],
    'cancelled' => [RaffleRegistrationStatus::Cancelled, false],
]);

it('restores a flagged registration to active with scoped success feedback', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create();
    $registration = persistedRaffleRegistration($raffle, [
        'status' => RaffleRegistrationStatus::Flagged,
    ]);

    expect($registration->canBeRestored())->toBeTrue();

    $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->post(adminRaffleUrl("/raffles/{$raffle->id}/registrations/{$registration->id}/restore"))
        ->assertRedirect(route('admin.raffles.registrations.index', $raffle))
        ->assertSessionHas(
            'admin.raffles.registration_status_restore_success',
            trans('admin-raffles.registrations.flash.restore_success'),
        )
        ->assertSessionHasNoErrors();

    assertDatabaseHas(RaffleRegistration::class, [
        'id' => $registration->id,
        'raffle_id' => $raffle->id,
        'status' => RaffleRegistrationStatus::Active->value,
    ]);
});

it('reports repeated restore as unavailable after restoring a flagged registration', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create();
    $registration = persistedRaffleRegistration($raffle, [
        'status' => RaffleRegistrationStatus::Flagged,
    ]);
    $restoreUrl = adminRaffleUrl("/raffles/{$raffle->id}/registrations/{$registration->id}/restore");

    $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->post($restoreUrl)
        ->assertRedirect(route('admin.raffles.registrations.index', $raffle))
        ->assertSessionHas('admin.raffles.registration_status_restore_success')
        ->assertSessionHasNoErrors();

    expect($registration->fresh()->status)->toBe(RaffleRegistrationStatus::Active);

    $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->post($restoreUrl)
        ->assertRedirect(route('admin.raffles.registrations.index', $raffle))
        ->assertSessionMissing('admin.raffles.registration_status_restore_success')
        ->assertSessionHasErrors(['registration_status' => 'Esta acción ya no está disponible para esta inscripción.']);

    expect($registration->fresh()->status)->toBe(RaffleRegistrationStatus::Active);
});

it('rejects restore for non-flagged registrations with unchanged status and scoped errors', function (RaffleRegistrationStatus $status) {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create();
    $registration = persistedRaffleRegistration($raffle, compact('status'));

    expect($registration->canBeRestored())->toBeFalse();

    $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->post(adminRaffleUrl("/raffles/{$raffle->id}/registrations/{$registration->id}/restore"))
        ->assertRedirect(route('admin.raffles.registrations.index', $raffle))
        ->assertSessionMissing('admin.raffles.registration_status_restore_success')
        ->assertSessionHasErrors(['registration_status' => 'Esta acción ya no está disponible para esta inscripción.']);

    assertDatabaseHas(RaffleRegistration::class, [
        'id' => $registration->id,
        'raffle_id' => $raffle->id,
        'status' => $status->value,
    ]);
})->with([
    'active' => [RaffleRegistrationStatus::Active],
    'cancelled' => [RaffleRegistrationStatus::Cancelled],
]);

it('returns bare not found when restore targets another raffles scope', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create();
    $otherRaffle = Raffle::factory()->create();
    $registration = persistedRaffleRegistration($otherRaffle, [
        'status' => RaffleRegistrationStatus::Flagged,
    ]);

    $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->post(adminRaffleUrl("/raffles/{$raffle->id}/registrations/{$registration->id}/restore"))
        ->assertNotFound()
        ->assertSessionMissing('admin.raffles.registration_status_restore_success')
        ->assertSessionHasNoErrors();

    assertDatabaseHas(RaffleRegistration::class, [
        'id' => $registration->id,
        'raffle_id' => $otherRaffle->id,
        'status' => RaffleRegistrationStatus::Flagged->value,
    ]);
});

it('redirects guests to the admin login page for html restore requests', function () {
    $raffle = Raffle::factory()->create();
    $registration = persistedRaffleRegistration($raffle, [
        'status' => RaffleRegistrationStatus::Flagged,
    ]);

    $this->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->post(adminRaffleUrl("/raffles/{$raffle->id}/registrations/{$registration->id}/restore"))
        ->assertRedirect(route('admin.login'));
});

it('returns 401 for unauthenticated json restore requests', function () {
    $raffle = Raffle::factory()->create();
    $registration = persistedRaffleRegistration($raffle, [
        'status' => RaffleRegistrationStatus::Flagged,
    ]);

    $this->withServerVariables([
        'HTTP_HOST' => adminRaffleHost(),
        'HTTP_ACCEPT' => 'application/json',
    ])->postJson(adminRaffleUrl("/raffles/{$raffle->id}/registrations/{$registration->id}/restore"))
        ->assertUnauthorized();
});

it('rejects get requests to the restore endpoint', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create();
    $registration = persistedRaffleRegistration($raffle, [
        'status' => RaffleRegistrationStatus::Flagged,
    ]);

    $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->get(adminRaffleUrl("/raffles/{$raffle->id}/registrations/{$registration->id}/restore"))
        ->assertMethodNotAllowed();

    expect($registration->fresh()->status)->toBe(RaffleRegistrationStatus::Flagged);
});

it('rejects nonnumeric restore route parameters', function (callable $path) {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create();
    $registration = persistedRaffleRegistration($raffle, [
        'status' => RaffleRegistrationStatus::Flagged,
    ]);

    $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->post(adminRaffleUrl($path($raffle, $registration)))
        ->assertNotFound();

    expect($registration->fresh()->status)->toBe(RaffleRegistrationStatus::Flagged);
})->with([
    'raffle parameter' => [fn (Raffle $raffle, RaffleRegistration $registration): string => "/raffles/not-a-number/registrations/{$registration->id}/restore"],
    'registration parameter' => [fn (Raffle $raffle): string => "/raffles/{$raffle->id}/registrations/not-a-number/restore"],
]);

it('keeps the named restore route in the web and admin authentication middleware', function () {
    $route = Route::getRoutes()->getByName('admin.raffles.registrations.restore');

    expect($route)->not->toBeNull()
        ->and($route->methods())->toBe(['POST'])
        ->and($route->gatherMiddleware())->toContain('web', 'auth:admin')
        ->and($route->wheres)->toMatchArray([
            'raffle' => '[0-9]+',
            'registration' => '[0-9]+',
        ]);
});

it('paginates authoritative registration snapshots with whole-raffle counts', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create();

    foreach (range(1, 27) as $position) {
        persistedRaffleRegistration($raffle, [
            'id' => $position,
            'name' => "Pagination Guest {$position}",
            'email' => "pagination-{$position}@example.com",
            'status' => match (true) {
                $position <= 20 => RaffleRegistrationStatus::Active,
                $position <= 24 => RaffleRegistrationStatus::Flagged,
                default => RaffleRegistrationStatus::Cancelled,
            },
        ]);
    }

    $url = adminRaffleUrl("/raffles/{$raffle->id}/registrations?page=2");
    $firstPage = $this->actingAs($admin, 'admin')->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->getJson(adminRaffleUrl("/raffles/{$raffle->id}/registrations"))->assertOk()->json();
    $json = $this->actingAs($admin, 'admin')->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->getJson($url)->assertOk()->json();

    expect(array_column($firstPage['rows'], 'id'))->toBe(range(27, 3))
        ->and(array_column($json['rows'], 'id'))->toBe([2, 1])
        ->and($json['counts'])->toBe(['active' => 20, 'flagged' => 4, 'cancelled' => 3, 'total' => 27])
        ->and($json['pagination'])->toMatchArray(['current' => 2, 'last' => 2, 'perPage' => 25, 'total' => 27]);

    $this->actingAs($admin, 'admin')->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->get($url)->assertOk()->assertViewHas('snapshot', $json)
        ->assertSee('Pagination Guest 2')->assertSee('Pagination Guest 1')
        ->assertDontSee('Pagination Guest 27');
});

it('bounds snapshot page links for very large registration totals', function () {
    $raffle = Raffle::factory()->create();
    $registrations = new LengthAwarePaginator([], 1_000_000, 25, 20_000);

    $links = RaffleRegistrationSnapshot::make($raffle, $registrations)['pagination']['links'];

    expect(array_column($links, 'page'))->toBe([1, 19_998, 19_999, 20_000, 20_001, 20_002, 40_000])
        ->and($links)->toHaveCount(7);
});

it('canonicalizes pagination for html and negotiated json', function (string $query, int $page, string $canonicalQuery) {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create();
    RaffleRegistration::factory()->count(26)->create(['raffle_id' => $raffle->id]);
    $base = adminRaffleUrl("/raffles/{$raffle->id}/registrations");

    $this->actingAs($admin, 'admin')->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->get($base.$query)->assertRedirect($base.$canonicalQuery);

    $this->actingAs($admin, 'admin')->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->getJson($base.$query)->assertOk()
        ->assertJsonPath('pagination.current', $page)
        ->assertJsonPath('pagination.canonicalUrl', $base.$canonicalQuery);
})->with([
    'explicit one' => ['?page=1', 1, ''],
    'malformed scalar' => ['?page=nope', 1, ''],
    'malformed array' => ['?page%5B%5D=2', 1, ''],
    'zero' => ['?page=0', 1, ''],
    'negative' => ['?page=-2', 1, ''],
    'over last' => ['?page=99', 2, '?page=2'],
]);

it('keeps complete pagination and status forms functional without javascript', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create();
    $older = persistedRaffleRegistration($raffle);
    RaffleRegistration::factory()->count(25)->create(['raffle_id' => $raffle->id]);
    $pageTwo = route('admin.raffles.registrations.index', [$raffle, 'page' => 2]);

    $this->actingAs($admin, 'admin')->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->get(route('admin.raffles.registrations.index', $raffle))->assertOk()->assertSee($pageTwo, escape: false);

    $this->actingAs($admin, 'admin')->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->get($pageTwo)->assertOk()
        ->assertSee(route('admin.raffles.registrations.flag', [$raffle, $older]), escape: false)
        ->assertSee('<input type="hidden" name="page" value="2">', escape: false)
        ->assertSee('<input type="hidden" name="_token"', escape: false);

    $this->actingAs($admin, 'admin')->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->post(route('admin.raffles.registrations.flag', [$raffle, $older]), ['page' => 2])
        ->assertRedirect($pageTwo)->assertSessionHas('admin.raffles.registration_status_flag_success');
});

it('embeds an xss-safe json snapshot', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create();
    persistedRaffleRegistration($raffle, ['name' => '</script><script>alert(1)</script>']);

    $content = $this->actingAs($admin, 'admin')->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->get(route('admin.raffles.registrations.index', $raffle))->assertOk()->getContent();

    preg_match('/<script id="raffle-registration-snapshot" type="application\/json">(.*?)<\/script>/s', $content, $match);
    $snapshot = json_decode($match[1] ?? '', true, flags: JSON_THROW_ON_ERROR);

    expect($snapshot['rows'][0]['name'])->toBe('</script><script>alert(1)</script>')
        ->and($match[1])->not->toContain('</script>');
});

it('rejects every invalid nested identity without mutation or snapshot', function (string $action, string $invalidPath) {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create();
    $otherRaffle = Raffle::factory()->create();
    $registration = persistedRaffleRegistration($otherRaffle, [
        'status' => $action === 'restore' ? RaffleRegistrationStatus::Flagged : RaffleRegistrationStatus::Active,
    ]);
    $path = strtr($invalidPath, [
        '{raffle}' => (string) $raffle->id,
        '{registration}' => (string) $registration->id,
        '{action}' => $action,
    ]);

    $this->actingAs($admin, 'admin')->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->postJson(adminRaffleUrl($path))->assertNotFound()->assertJsonMissingPath('snapshot');

    expect($registration->fresh()->status)->toBe(
        $action === 'restore' ? RaffleRegistrationStatus::Flagged : RaffleRegistrationStatus::Active,
    );
})->with(fn () => collect(['flag', 'cancel', 'restore'])->crossJoin([
    '/raffles/nope/registrations/{registration}/{action}',
    '/raffles/{raffle}/registrations/nope/{action}',
    '/raffles/{raffle}/registrations/999999/{action}',
    '/raffles/{raffle}/registrations/{registration}/{action}',
    '/raffles/{raffle}/registrations/{action}',
])->mapWithKeys(fn (array $case) => [implode(' ', $case) => $case])->all());
