<?php

use App\Models\Admin;
use App\Models\Raffle;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

function raffleCreateAdminHost(): string
{
    return (string) parse_url((string) config('app.admin_url'), PHP_URL_HOST);
}

function raffleCreateAdminUrl(string $path = '/'): string
{
    return rtrim((string) config('app.admin_url'), '/').$path;
}

it('redirects guests to the admin login page for html raffle create requests', function () {
    $this->withServerVariables(['HTTP_HOST' => raffleCreateAdminHost()])
        ->get(raffleCreateAdminUrl('/raffles/create'))
        ->assertRedirect(route('admin.login'));
});

it('returns 401 for unauthenticated json raffle create requests', function () {
    $this->withServerVariables([
        'HTTP_HOST' => raffleCreateAdminHost(),
        'HTTP_ACCEPT' => 'application/json',
    ])->getJson(raffleCreateAdminUrl('/raffles/create'))
        ->assertUnauthorized();
});

it('redirects guests to the admin login page for html raffle store requests', function () {
    $this->withServerVariables(['HTTP_HOST' => raffleCreateAdminHost()])
        ->post(raffleCreateAdminUrl('/raffles'), [])
        ->assertRedirect(route('admin.login'));
});

it('returns 401 for unauthenticated json raffle store requests', function () {
    $this->withServerVariables([
        'HTTP_HOST' => raffleCreateAdminHost(),
        'HTTP_ACCEPT' => 'application/json',
    ])->postJson(raffleCreateAdminUrl('/raffles'), [])
        ->assertUnauthorized();
});

it('shows the raffle create page to authenticated admins', function () {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => raffleCreateAdminHost()])
        ->get(raffleCreateAdminUrl('/raffles/create'))
        ->assertOk()
        ->assertSeeText('Crear sorteo')
        ->assertSee('name="starts_at"', escape: false)
        ->assertSee('name="ends_at"', escape: false)
        ->assertSee('type="datetime-local"', escape: false);
});

it('persists blank availability values as null', function () {
    $admin = Admin::factory()->create();

    $response = $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => raffleCreateAdminHost()])
        ->from(raffleCreateAdminUrl('/raffles/create'))
        ->post(raffleCreateAdminUrl('/raffles'), [
            'starts_at' => '',
            'ends_at' => '',
        ]);

    $response->assertRedirect(route('admin.raffles.index'))
        ->assertSessionHas('admin.raffles.create_success');

    assertDatabaseHas(Raffle::class, [
        'starts_at' => null,
        'ends_at' => null,
        'status' => 'draft',
    ]);
});

it('returns validation errors and old input for invalid availability values', function () {
    $admin = Admin::factory()->create();

    $response = $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => raffleCreateAdminHost()])
        ->from(raffleCreateAdminUrl('/raffles/create'))
        ->post(raffleCreateAdminUrl('/raffles'), [
            'starts_at' => 'not-a-date',
            'ends_at' => '2026-06-28T18:45',
        ]);

    $response->assertRedirect(raffleCreateAdminUrl('/raffles/create'))
        ->assertSessionHasErrors('starts_at')
        ->assertSessionHasInput('starts_at', 'not-a-date')
        ->assertSessionHasInput('ends_at', '2026-06-28T18:45');

    expect(Raffle::query()->count())->toBe(0);
});

it('creates a draft raffle with valid datetime-local availability values', function () {
    $admin = Admin::factory()->create();

    $response = $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => raffleCreateAdminHost()])
        ->post(raffleCreateAdminUrl('/raffles'), [
            'starts_at' => '2026-06-21T11:00',
            'ends_at' => '2026-06-28T19:30',
        ]);

    $response->assertRedirect(route('admin.raffles.index'))
        ->assertSessionHas('admin.raffles.create_success');

    $raffle = Raffle::query()->sole();

    expect($raffle->status->value)->toBe('draft')
        ->and($raffle->starts_at?->format('Y-m-d\TH:i'))->toBe('2026-06-21T11:00')
        ->and($raffle->ends_at?->format('Y-m-d\TH:i'))->toBe('2026-06-28T19:30');
});
