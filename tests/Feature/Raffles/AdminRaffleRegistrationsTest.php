<?php

use App\Models\Admin;
use App\Models\Raffle;
use App\Models\RaffleRegistration;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function raffleRegistrationsAdminHost(): string
{
    return (string) parse_url((string) config('app.admin_url'), PHP_URL_HOST);
}

function raffleRegistrationsAdminUrl(string $path = '/'): string
{
    return rtrim((string) config('app.admin_url'), '/').$path;
}

it('redirects guests to the admin login page for html raffle registration list requests', function () {
    $raffle = Raffle::factory()->create();

    $this->withServerVariables(['HTTP_HOST' => raffleRegistrationsAdminHost()])
        ->get(raffleRegistrationsAdminUrl("/raffles/{$raffle->id}/registrations"))
        ->assertRedirect(route('admin.login'));
});

it('returns 401 for unauthenticated json raffle registration list requests', function () {
    $raffle = Raffle::factory()->create();

    $this->withServerVariables([
        'HTTP_HOST' => raffleRegistrationsAdminHost(),
        'HTTP_ACCEPT' => 'application/json',
    ])->getJson(raffleRegistrationsAdminUrl("/raffles/{$raffle->id}/registrations"))
        ->assertUnauthorized();
});

it('shows an explicit empty state for authenticated admins when a raffle has no registrations', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create();

    $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => raffleRegistrationsAdminHost()])
        ->get(raffleRegistrationsAdminUrl("/raffles/{$raffle->id}/registrations"))
        ->assertOk()
        ->assertSeeText("Inscripciones del sorteo #{$raffle->id}")
        ->assertSeeText('Todavía no hay inscripciones para este sorteo.')
        ->assertDontSeeText('Exportar')
        ->assertDontSeeText('Abrir participación')
        ->assertDontSeeText('Cerrar participación');
});

it('shows existing registrations newest-first with allowed fields and read-only linked-account signals', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create();
    $linkedUser = User::factory()->create();

    $olderRegistration = RaffleRegistration::factory()->create([
        'raffle_id' => $raffle->id,
        'user_id' => null,
        'name' => 'Older Guest',
        'email' => 'OLDER@example.com',
    ]);
    $olderRegistration->forceFill([
        'created_at' => CarbonImmutable::parse('2026-07-01 09:15:00'),
    ])->save();

    $newerRegistration = RaffleRegistration::factory()->create([
        'raffle_id' => $raffle->id,
        'user_id' => $linkedUser->id,
        'name' => 'Newer Guest',
        'email' => 'NEWER@example.com',
    ]);
    $newerRegistration->forceFill([
        'created_at' => CarbonImmutable::parse('2026-07-02 11:45:00'),
    ])->save();

    $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => raffleRegistrationsAdminHost()])
        ->get(raffleRegistrationsAdminUrl("/raffles/{$raffle->id}/registrations"))
        ->assertOk()
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
        ->assertDontSeeText('Exportar')
        ->assertDontSeeText('Eliminar')
        ->assertDontSeeText('Editar')
        ->assertDontSeeText('Abrir participación')
        ->assertDontSeeText('Cerrar participación');
});
