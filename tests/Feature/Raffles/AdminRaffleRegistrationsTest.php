<?php

use App\Models\Admin;
use App\Models\Raffle;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
        ->assertSeeText('0 inscripciones registradas para este sorteo.')
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
            '2026-07-02 11:45',
            'Cuenta vinculada',
            'Older Guest',
            'older@example.com',
            '2026-07-01 09:15',
            'Sin cuenta vinculada',
        ], escape: false)
        ->assertDontSeeText('Todavía no hay inscripciones para este sorteo.')
        ->assertDontSeeText('Ticket')
        ->assertDontSeeText('Pago')
        ->assertDontSeeText('Ganador')
        ->assertDontSeeText('Aprobar')
        ->assertDontSeeText('Rechazar')
        ->assertDontSeeText('Cancelar inscripción')
        ->assertDontSeeText('Marcar como observada')
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
        ->assertSeeText('2 inscripciones registradas para este sorteo.')
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
