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
        ->assertSee('name="title"', escape: false)
        ->assertSee('maxlength="100"', escape: false)
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
            'title' => str_repeat('T', 100),
            'starts_at' => '',
            'ends_at' => '',
        ]);

    $response->assertRedirect(route('admin.raffles.index'))
        ->assertSessionHas('admin.raffles.create_success');

    assertDatabaseHas(Raffle::class, [
        'title' => str_repeat('T', 100),
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
            'title' => 'Valid title',
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
            'title' => 'Summer raffle',
            'starts_at' => '2026-06-21T11:00',
            'ends_at' => '2026-06-28T19:30',
        ]);

    $response->assertRedirect(route('admin.raffles.index'))
        ->assertSessionHas('admin.raffles.create_success');

    $raffle = Raffle::query()->sole();

    expect($raffle->status->value)->toBe('draft')
        ->and($raffle->title)->toBe('Summer raffle')
        ->and($raffle->starts_at?->format('Y-m-d\TH:i'))->toBe('2026-06-21T11:00')
        ->and($raffle->ends_at?->format('Y-m-d\TH:i'))->toBe('2026-06-28T19:30');
});

it('normalizes and accepts duplicate raffle titles', function () {
    $admin = Admin::factory()->create();
    $existing = Raffle::factory()->create(['title' => 'Summer raffle']);

    $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => raffleCreateAdminHost()])
        ->post(raffleCreateAdminUrl('/raffles'), [
            'title' => '  Summer raffle  ',
            'starts_at' => '',
            'ends_at' => '',
        ])->assertRedirect(route('admin.raffles.index'));

    expect(Raffle::query()->where('title', 'Summer raffle')->pluck('id')->all())
        ->toHaveCount(2)
        ->toContain($existing->id);
});

it('rejects invalid titles and returns normalized old input', function (mixed $title, mixed $oldInput) {
    $admin = Admin::factory()->create();
    $payload = ['starts_at' => '', 'ends_at' => ''];

    if ($title !== '__missing__') {
        $payload['title'] = $title;
    }

    $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => raffleCreateAdminHost()])
        ->from(raffleCreateAdminUrl('/raffles/create'))
        ->post(raffleCreateAdminUrl('/raffles'), $payload)
        ->assertRedirect(raffleCreateAdminUrl('/raffles/create'))
        ->assertSessionHasErrors('title');

    expect(session()->getOldInput('title', '__missing__'))->toBe($oldInput)
        ->and(Raffle::query()->count())->toBe(0);
})->with([
    'missing' => ['__missing__', '__missing__'],
    'whitespace only' => ['   ', null],
    'non-string' => [['invalid'], ['invalid']],
    '101 characters' => [str_repeat('T', 101), str_repeat('T', 101)],
]);
