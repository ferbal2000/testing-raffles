<?php

use App\Models\Admin;
use App\Models\Raffle;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

function raffleAdminHost(): string
{
    return (string) parse_url((string) config('app.admin_url'), PHP_URL_HOST);
}

function raffleAdminUrl(string $path = '/'): string
{
    return rtrim((string) config('app.admin_url'), '/').$path;
}

function raffleDateTime(?CarbonImmutable $value): string
{
    return $value?->format('Y-m-d H:i') ?? 'Sin definir';
}

function raffleIndexResponse(?Admin $admin = null, array $server = []): TestResponse
{
    $request = test();

    if ($admin !== null) {
        $request = $request->actingAs($admin, 'admin');
    }

    return $request
        ->withServerVariables(array_merge(['HTTP_HOST' => raffleAdminHost()], $server))
        ->get(raffleAdminUrl('/raffles'));
}

function persistedRaffleForIndex(string $status, ?CarbonImmutable $startsAt, ?CarbonImmutable $endsAt, CarbonImmutable $createdAt): Raffle
{
    $factory = match ($status) {
        'published' => Raffle::factory()->published(),
        'closed' => Raffle::factory()->closed(),
        default => Raffle::factory(),
    };

    $raffle = $factory->create([
        'starts_at' => $startsAt,
        'ends_at' => $endsAt,
    ]);

    $raffle->forceFill(['created_at' => $createdAt])->save();

    return $raffle->fresh();
}

it('redirects guests to the admin login page for html raffle index requests', function () {
    raffleIndexResponse()
        ->assertRedirect(route('admin.login'));
});

it('returns 401 for unauthenticated json raffle index requests', function () {
    raffleIndexResponse(server: [
        'HTTP_ACCEPT' => 'application/json',
    ])
        ->assertUnauthorized();
});

it('shows the raffle index page to authenticated admins', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create();

    raffleIndexResponse($admin)
        ->assertOk()
        ->assertSee(route('admin.raffles.create'), escape: false)
        ->assertSeeText('Crear sorteo')
        ->assertSee(route('admin.raffles.edit', $raffle), escape: false)
        ->assertSeeText('Editar');
});

it('shows an explicit empty state when no raffles exist', function () {
    $admin = Admin::factory()->create();

    raffleIndexResponse($admin)
        ->assertOk()
        ->assertSeeText('Aún no hay sorteos cargados.')
        ->assertDontSeeText('Borrador');
});

it('lists persisted raffles in newest-first order with the required fields', function () {
    $admin = Admin::factory()->create();

    $olderStartsAt = CarbonImmutable::parse('2026-06-20 10:00:00');
    $olderEndsAt = CarbonImmutable::parse('2026-06-25 18:00:00');
    $olderCreatedAt = CarbonImmutable::parse('2026-06-18 09:15:00');

    $olderRaffle = persistedRaffleForIndex('draft', $olderStartsAt, $olderEndsAt, $olderCreatedAt);

    $newerStartsAt = CarbonImmutable::parse('2026-06-21 11:00:00');
    $newerEndsAt = CarbonImmutable::parse('2026-06-28 19:30:00');
    $newerCreatedAt = CarbonImmutable::parse('2026-06-19 14:45:00');

    $newerRaffle = persistedRaffleForIndex('closed', $newerStartsAt, $newerEndsAt, $newerCreatedAt);

    raffleIndexResponse($admin)
        ->assertOk()
        ->assertSeeInOrder([
            (string) $newerRaffle->id,
            'closed',
            raffleDateTime($newerStartsAt),
            raffleDateTime($newerEndsAt),
            raffleDateTime($newerCreatedAt),
            (string) $olderRaffle->id,
            'draft',
            raffleDateTime($olderStartsAt),
            raffleDateTime($olderEndsAt),
            raffleDateTime($olderCreatedAt),
        ], escape: false);
});

it('renders safe placeholders for nullable raffle availability values', function () {
    $admin = Admin::factory()->create();
    $createdAt = CarbonImmutable::parse('2026-06-20 08:00:00');

    $raffle = persistedRaffleForIndex('published', null, null, $createdAt);

    raffleIndexResponse($admin)
        ->assertOk()
        ->assertSeeText((string) $raffle->id)
        ->assertSeeText('published')
        ->assertSeeText(raffleDateTime($createdAt))
        ->assertSeeText('Sin definir')
        ->assertDontSeeText('2026-06-20 10:00')
        ->assertDontSeeText('2026-06-25 18:00');
});

it('shows a scoped create success flash after a successful create redirect', function () {
    $admin = Admin::factory()->create();

    test()
        ->withSession([
            'admin.raffles.create_success' => 'El sorteo se creó en borrador.',
        ])
        ->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => raffleAdminHost()])
        ->get(raffleAdminUrl('/raffles'))
        ->assertOk()
        ->assertSeeText('El sorteo se creó en borrador.');
});

it('shows a scoped update success flash after a successful update redirect', function () {
    $admin = Admin::factory()->create();

    test()
        ->withSession([
            'admin.raffles.update_success' => 'El sorteo se actualizó.',
        ])
        ->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => raffleAdminHost()])
        ->get(raffleAdminUrl('/raffles'))
        ->assertOk()
        ->assertSeeText('El sorteo se actualizó.')
        ->assertDontSeeText('El sorteo se creó en borrador.');
});

it('shows participation actions only for eligible raffle rows', function () {
    $admin = Admin::factory()->create();
    $openableRaffle = Raffle::factory()->published()->create();
    $closableRaffle = Raffle::factory()->published()->openedForParticipation()->create();
    $draftRaffle = Raffle::factory()->create();
    $closedRaffle = Raffle::factory()->published()->openedForParticipation()->create();
    $closedRaffle->close();
    $participationClosedRaffle = Raffle::factory()->published()->participationClosed()->create();

    raffleIndexResponse($admin)
        ->assertOk()
        ->assertSee(route('admin.raffles.participation.open', $openableRaffle), escape: false)
        ->assertSeeText('Abrir participación')
        ->assertSee(route('admin.raffles.participation.close', $closableRaffle), escape: false)
        ->assertSeeText('Cerrar participación')
        ->assertDontSee(route('admin.raffles.participation.open', $draftRaffle), escape: false)
        ->assertDontSee(route('admin.raffles.participation.close', $draftRaffle), escape: false)
        ->assertDontSee(route('admin.raffles.participation.open', $closedRaffle), escape: false)
        ->assertDontSee(route('admin.raffles.participation.close', $closedRaffle), escape: false)
        ->assertDontSee(route('admin.raffles.participation.open', $participationClosedRaffle), escape: false)
        ->assertDontSee(route('admin.raffles.participation.close', $participationClosedRaffle), escape: false);
});

it('shows a scoped participation open success flash after a matching redirect', function () {
    $admin = Admin::factory()->create();

    test()
        ->withSession([
            'admin.raffles.participation_open_success' => 'La participación del sorteo se abrió.',
        ])
        ->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => raffleAdminHost()])
        ->get(raffleAdminUrl('/raffles'))
        ->assertOk()
        ->assertSeeText('La participación del sorteo se abrió.')
        ->assertDontSeeText('La participación del sorteo se cerró.');
});

it('shows a scoped participation close success flash after a matching redirect', function () {
    $admin = Admin::factory()->create();

    test()
        ->withSession([
            'admin.raffles.participation_close_success' => 'La participación del sorteo se cerró.',
        ])
        ->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => raffleAdminHost()])
        ->get(raffleAdminUrl('/raffles'))
        ->assertOk()
        ->assertSeeText('La participación del sorteo se cerró.')
        ->assertDontSeeText('La participación del sorteo se abrió.');
});

it('shows the controller-reported participation error flashed by invalid transitions', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()->create();

    $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => raffleAdminHost()])
        ->from(route('admin.raffles.index'))
        ->post(raffleAdminUrl("/raffles/{$raffle->id}/participation/open"))
        ->assertRedirect(route('admin.raffles.index'));

    raffleIndexResponse($admin)
        ->assertOk()
        ->assertSeeText('Cannot transition raffle from [draft] to [participation_open].');
});

it('does not show create or update success flashes without scoped session keys', function () {
    $admin = Admin::factory()->create();

    raffleIndexResponse($admin)
        ->assertOk()
        ->assertDontSeeText('El sorteo se creó en borrador.')
        ->assertDontSeeText('El sorteo se actualizó.')
        ->assertDontSeeText('La participación del sorteo se abrió.')
        ->assertDontSeeText('La participación del sorteo se cerró.');
});
