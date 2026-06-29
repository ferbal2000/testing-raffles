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
    $visibleRaffle = Raffle::factory()->published()->create();
    $draftRaffle = Raffle::factory()->create();
    $closedRaffle = Raffle::factory()->closed()->create();

    $this->withServerVariables(['HTTP_HOST' => publicCatalogHost()])
        ->get(publicCatalogUrl())
        ->assertOk()
        ->assertSeeText("Sorteo #{$visibleRaffle->id}")
        ->assertSee('href="/raffles/'.$visibleRaffle->id.'"', false)
        ->assertDontSeeText("Sorteo #{$draftRaffle->id}")
        ->assertDontSeeText("Sorteo #{$closedRaffle->id}");
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
        ->create();

    $newerRaffle = Raffle::factory()
        ->published()
        ->participationClosed(
            CarbonImmutable::parse('2026-07-02 09:00:00'),
            CarbonImmutable::parse('2026-07-03 21:00:00'),
        )
        ->scheduled(CarbonImmutable::parse('2026-09-01 10:00:00'), CarbonImmutable::parse('2026-09-15 18:00:00'))
        ->create();

    $this->withServerVariables(['HTTP_HOST' => publicCatalogHost()])
        ->get(publicCatalogUrl())
        ->assertOk()
        ->assertSeeInOrder([
            'href="/raffles/'.$newerRaffle->id.'"',
            'href="/raffles/'.$olderRaffle->id.'"',
        ], false)
        ->assertSeeText("Sorteo #{$newerRaffle->id}")
        ->assertSeeText('Participación no disponible')
        ->assertSeeText("Sorteo #{$olderRaffle->id}")
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
