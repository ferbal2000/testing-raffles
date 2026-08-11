<?php

use App\Models\Raffle;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function publicCatalogHost(): string
{
    return (string) parse_url((string) config('app.public_url'), PHP_URL_HOST);
}

function publicCatalogUrl(string $path = ''): string
{
    return rtrim((string) config('app.public_url'), '/').$path;
}

beforeEach(function () {
    app()->setLocale('es');
});

it('shows only published raffles in the public catalog', function () {
    $visibleRaffle = Raffle::factory()->published()->create(['title' => 'Sorteo visible']);
    $draftRaffle = Raffle::factory()->create(['title' => 'Sorteo borrador']);
    $closedRaffle = Raffle::factory()->closed()->create(['title' => 'Sorteo cerrado']);

    $this->withServerVariables(['HTTP_HOST' => publicCatalogHost()])
        ->get(publicCatalogUrl())
        ->assertOk()
        ->assertSeeText('Sorteo visible')
        ->assertSeeText("ID del sorteo: {$visibleRaffle->id}")
        ->assertSee('href="/raffles/'.$visibleRaffle->id.'"', false)
        ->assertDontSeeText('Sorteo borrador')
        ->assertDontSeeText('Sorteo cerrado');
});

it('renders authored markup in a visible raffle title as escaped text', function () {
    $title = '<script>alert("catalog")</script> Premio público';
    Raffle::factory()->published()->create(['title' => $title]);

    $this->withServerVariables(['HTTP_HOST' => publicCatalogHost()])
        ->get(publicCatalogUrl())
        ->assertOk()
        ->assertSeeText($title)
        ->assertSee('&lt;script&gt;alert(&quot;catalog&quot;)&lt;/script&gt; Premio público', escape: false)
        ->assertDontSee('<script>alert("catalog")</script>', escape: false);
});

it('keeps backfilled title, numeric id, detail link, and descending id order', function () {
    $backfilledRaffle = Raffle::factory()->published()->create([
        'id' => 42,
        'title' => 'Sorteo #42',
    ]);
    $newerRaffle = Raffle::factory()->published()->create([
        'id' => 43,
        'title' => 'Premio más nuevo',
    ]);

    $this->withServerVariables(['HTTP_HOST' => publicCatalogHost()])
        ->get(publicCatalogUrl())
        ->assertOk()
        ->assertSeeText('Sorteo #42')
        ->assertSeeText('ID del sorteo: 42')
        ->assertSee('href="/raffles/'.$backfilledRaffle->id.'"', false)
        ->assertSeeInOrder([
            'Premio más nuevo',
            'href="/raffles/'.$newerRaffle->id.'"',
            'Sorteo #42',
            'href="/raffles/'.$backfilledRaffle->id.'"',
        ], false);
});

it('shows an explicit empty state when no published raffles are available', function () {
    Raffle::factory()->create();
    Raffle::factory()->closed()->create();

    $this->withServerVariables(['HTTP_HOST' => publicCatalogHost()])
        ->get(publicCatalogUrl())
        ->assertOk()
        ->assertSeeText('No hay sorteos publicados en este momento.')
        ->assertDontSee('href="/raffles/', false);
});

it('orders catalog cards by descending id and keeps cards lean', function () {
    $olderRaffle = Raffle::factory()
        ->published()
        ->openedForParticipation(CarbonImmutable::parse('2026-07-01 09:00:00'))
        ->scheduled(CarbonImmutable::parse('2026-08-10 10:00:00'), CarbonImmutable::parse('2026-08-20 18:00:00'))
        ->create(['title' => 'Premio anterior']);

    $newerRaffle = Raffle::factory()
        ->published()
        ->participationClosed(
            CarbonImmutable::parse('2026-07-02 09:00:00'),
            CarbonImmutable::parse('2026-07-03 21:00:00'),
        )
        ->scheduled(CarbonImmutable::parse('2026-09-01 10:00:00'), CarbonImmutable::parse('2026-09-15 18:00:00'))
        ->create(['title' => 'Premio reciente']);

    $this->withServerVariables(['HTTP_HOST' => publicCatalogHost()])
        ->get(publicCatalogUrl())
        ->assertOk()
        ->assertSeeInOrder([
            'href="/raffles/'.$newerRaffle->id.'"',
            'href="/raffles/'.$olderRaffle->id.'"',
        ], false)
        ->assertSeeText('Premio reciente')
        ->assertSeeText("ID del sorteo: {$newerRaffle->id}")
        ->assertSeeText('Participación no disponible')
        ->assertSeeText('Premio anterior')
        ->assertSeeText("ID del sorteo: {$olderRaffle->id}")
        ->assertSeeText('Participación disponible')
        ->assertDontSee('type="search"', false)
        ->assertDontSee('name="search"', false)
        ->assertDontSee('?page=', false)
        ->assertDontSeeText('Filtrar')
        ->assertDontSeeText('Participá ahora')
        ->assertDontSeeText('10/08/2026 10:00')
        ->assertDontSeeText('20/08/2026 18:00')
        ->assertDontSeeText('01/09/2026 10:00')
        ->assertDontSeeText('15/09/2026 18:00');
});
