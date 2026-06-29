<?php

use App\Models\Raffle;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function publicRaffleHost(): string
{
    return (string) parse_url((string) config('app.public_url'), PHP_URL_HOST);
}

function publicRaffleUrl(string $path = ''): string
{
    return rtrim((string) config('app.public_url'), '/').$path;
}

beforeEach(function () {
    app()->setLocale('es');
});

it('shows the public raffle detail page for published raffles only', function () {
    $publishedRaffle = Raffle::factory()->published()->create();
    $draftRaffle = Raffle::factory()->create();
    $closedRaffle = Raffle::factory()->closed()->create();

    $this->withServerVariables(['HTTP_HOST' => publicRaffleHost()])
        ->get(publicRaffleUrl("/raffles/{$publishedRaffle->id}"))
        ->assertOk();

    $this->withServerVariables(['HTTP_HOST' => publicRaffleHost()])
        ->get(publicRaffleUrl("/raffles/{$draftRaffle->id}"))
        ->assertNotFound();

    $this->withServerVariables(['HTTP_HOST' => publicRaffleHost()])
        ->get(publicRaffleUrl("/raffles/{$closedRaffle->id}"))
        ->assertNotFound();

    $this->withServerVariables(['HTTP_HOST' => publicRaffleHost()])
        ->get(publicRaffleUrl('/raffles/not-a-number'))
        ->assertNotFound();
});

it('shows read-only availability messaging without registration or ticket actions', function () {
    $openRaffle = Raffle::factory()
        ->published()
        ->openedForParticipation(CarbonImmutable::parse('2026-07-01 09:00:00'))
        ->create();

    $closedParticipationRaffle = Raffle::factory()
        ->published()
        ->participationClosed(
            CarbonImmutable::parse('2026-07-01 09:00:00'),
            CarbonImmutable::parse('2026-07-03 21:00:00'),
        )
        ->create();

    $this->withServerVariables(['HTTP_HOST' => publicRaffleHost()])
        ->get(publicRaffleUrl("/raffles/{$openRaffle->id}"))
        ->assertOk()
        ->assertSeeText('Participación disponible')
        ->assertDontSeeText('Registrate')
        ->assertDontSeeText('Comprar ticket')
        ->assertDontSeeText((string) $openRaffle->id);

    $this->withServerVariables(['HTTP_HOST' => publicRaffleHost()])
        ->get(publicRaffleUrl("/raffles/{$closedParticipationRaffle->id}"))
        ->assertOk()
        ->assertSeeText('Participación no disponible')
        ->assertDontSeeText('Registrate')
        ->assertDontSeeText('Comprar ticket');
});

it('treats starts and ends dates as informational metadata only', function () {
    $upcomingRaffle = Raffle::factory()
        ->published()
        ->scheduled(CarbonImmutable::parse('2026-08-10 10:00:00'), CarbonImmutable::parse('2026-08-20 18:00:00'))
        ->create();

    $endedWindowRaffle = Raffle::factory()
        ->published()
        ->openedForParticipation(CarbonImmutable::parse('2026-07-01 09:00:00'))
        ->scheduled(CarbonImmutable::parse('2026-06-01 10:00:00'), CarbonImmutable::parse('2026-06-05 18:00:00'))
        ->create();

    $this->withServerVariables(['HTTP_HOST' => publicRaffleHost()])
        ->get(publicRaffleUrl("/raffles/{$upcomingRaffle->id}"))
        ->assertOk()
        ->assertSeeText('Participación no disponible')
        ->assertSeeText('10/08/2026 10:00')
        ->assertSeeText('20/08/2026 18:00');

    $this->withServerVariables(['HTTP_HOST' => publicRaffleHost()])
        ->get(publicRaffleUrl("/raffles/{$endedWindowRaffle->id}"))
        ->assertOk()
        ->assertSeeText('Participación disponible')
        ->assertSeeText('01/06/2026 10:00')
        ->assertSeeText('05/06/2026 18:00');
});
