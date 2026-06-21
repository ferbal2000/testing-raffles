<?php

use App\Models\Admin;
use App\Models\Raffle;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

function raffleEditAdminHost(): string
{
    return (string) parse_url((string) config('app.admin_url'), PHP_URL_HOST);
}

function raffleEditAdminUrl(string $path = '/'): string
{
    return rtrim((string) config('app.admin_url'), '/').$path;
}

it('redirects guests to the admin login page for html raffle edit requests', function () {
    $raffle = Raffle::factory()->create();

    $this->withServerVariables(['HTTP_HOST' => raffleEditAdminHost()])
        ->get(raffleEditAdminUrl("/raffles/{$raffle->id}/edit"))
        ->assertRedirect(route('admin.login'));
});

it('returns 401 for unauthenticated json raffle edit requests', function () {
    $raffle = Raffle::factory()->create();

    $this->withServerVariables([
        'HTTP_HOST' => raffleEditAdminHost(),
        'HTTP_ACCEPT' => 'application/json',
    ])->getJson(raffleEditAdminUrl("/raffles/{$raffle->id}/edit"))
        ->assertUnauthorized();
});

it('redirects guests to the admin login page for html raffle update requests', function () {
    $raffle = Raffle::factory()->create();

    $this->withServerVariables(['HTTP_HOST' => raffleEditAdminHost()])
        ->patch(raffleEditAdminUrl("/raffles/{$raffle->id}"), [])
        ->assertRedirect(route('admin.login'));
});

it('returns 401 for unauthenticated json raffle update requests', function () {
    $raffle = Raffle::factory()->create();

    $this->withServerVariables([
        'HTTP_HOST' => raffleEditAdminHost(),
        'HTTP_ACCEPT' => 'application/json',
    ])->patchJson(raffleEditAdminUrl("/raffles/{$raffle->id}"), [])
        ->assertUnauthorized();
});

it('shows the raffle edit page to authenticated admins', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create([
        'starts_at' => CarbonImmutable::parse('2026-06-21 11:00:00'),
        'ends_at' => CarbonImmutable::parse('2026-06-28 19:30:00'),
    ]);

    $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => raffleEditAdminHost()])
        ->get(raffleEditAdminUrl("/raffles/{$raffle->id}/edit"))
        ->assertOk()
        ->assertSeeText('Editar sorteo')
        ->assertSee('action="'.route('admin.raffles.update', $raffle).'"', escape: false)
        ->assertSee('name="starts_at"', escape: false)
        ->assertSee('value="2026-06-21T11:00"', escape: false)
        ->assertSee('name="ends_at"', escape: false)
        ->assertSee('value="2026-06-28T19:30"', escape: false)
        ->assertDontSee('name="status"', escape: false);
});

it('returns validation errors and old input for invalid availability values', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create();

    $response = $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => raffleEditAdminHost()])
        ->from(raffleEditAdminUrl("/raffles/{$raffle->id}/edit"))
        ->patch(raffleEditAdminUrl("/raffles/{$raffle->id}"), [
            'starts_at' => 'invalid-date',
            'ends_at' => '2026-06-28T18:45',
        ]);

    $response->assertRedirect(raffleEditAdminUrl("/raffles/{$raffle->id}/edit"))
        ->assertSessionHasErrors('starts_at')
        ->assertSessionHasInput('starts_at', 'invalid-date')
        ->assertSessionHasInput('ends_at', '2026-06-28T18:45');
});

it('persists blank availability values as null on update', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create([
        'starts_at' => CarbonImmutable::parse('2026-06-21 11:00:00'),
        'ends_at' => CarbonImmutable::parse('2026-06-28 19:30:00'),
    ]);

    $response = $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => raffleEditAdminHost()])
        ->patch(raffleEditAdminUrl("/raffles/{$raffle->id}"), [
            'starts_at' => '',
            'ends_at' => '',
        ]);

    $response->assertRedirect(route('admin.raffles.index'))
        ->assertSessionHas('admin.raffles.update_success');

    assertDatabaseHas(Raffle::class, [
        'id' => $raffle->id,
        'starts_at' => null,
        'ends_at' => null,
        'status' => 'draft',
    ]);
});

it('updates raffle availability for draft, published, and closed statuses', function (string $status) {
    $admin = Admin::factory()->create();

    $factory = match ($status) {
        'published' => Raffle::factory()->published(),
        'closed' => Raffle::factory()->closed(),
        default => Raffle::factory(),
    };

    $raffle = $factory->create();

    $response = $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => raffleEditAdminHost()])
        ->patch(raffleEditAdminUrl("/raffles/{$raffle->id}"), [
            'starts_at' => '2026-07-01T09:15',
            'ends_at' => '2026-07-10T18:45',
        ]);

    $response->assertRedirect(route('admin.raffles.index'))
        ->assertSessionHas('admin.raffles.update_success');

    $raffle->refresh();

    expect($raffle->status->value)->toBe($status)
        ->and($raffle->starts_at?->format('Y-m-d\TH:i'))->toBe('2026-07-01T09:15')
        ->and($raffle->ends_at?->format('Y-m-d\TH:i'))->toBe('2026-07-10T18:45');
})->with(['draft', 'published', 'closed']);
