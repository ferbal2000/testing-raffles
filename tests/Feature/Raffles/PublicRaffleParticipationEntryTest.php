<?php

use App\Models\Raffle;
use App\Models\RaffleRegistration;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

function publicParticipationHost(): string
{
    return (string) parse_url((string) config('app.public_url'), PHP_URL_HOST);
}

function publicParticipationUrl(string $path = ''): string
{
    return rtrim((string) config('app.public_url'), '/').$path;
}

beforeEach(function () {
    app()->setLocale('es');
});

it('normalizes emails when registrations are created directly through the model boundary', function () {
    $raffle = Raffle::factory()
        ->published()
        ->openedForParticipation(CarbonImmutable::parse('2026-07-01 09:00:00'))
        ->create();

    $registration = RaffleRegistration::query()->create([
        'raffle_id' => $raffle->id,
        'user_id' => null,
        'name' => 'Ada Lovelace',
        'email' => '  ADA@Example.COM ',
    ]);

    expect($registration->fresh()->email)->toBe('ada@example.com');

    assertDatabaseHas(RaffleRegistration::class, [
        'raffle_id' => $raffle->id,
        'email' => 'ada@example.com',
    ]);
});

it('accepts an eligible guest submission and stores a normalized registration', function () {
    $raffle = Raffle::factory()
        ->published()
        ->openedForParticipation(CarbonImmutable::parse('2026-07-01 09:00:00'))
        ->create();

    $response = $this->withServerVariables(['HTTP_HOST' => publicParticipationHost()])
        ->post(publicParticipationUrl("/raffles/{$raffle->id}/participation"), [
            'name' => 'Ada Lovelace',
            'email' => '  ADA@Example.COM ',
        ]);

    $response->assertRedirect(route('public.raffles.show', $raffle))
        ->assertSessionHas('public.raffles.participation_success');

    assertDatabaseHas(RaffleRegistration::class, [
        'raffle_id' => $raffle->id,
        'user_id' => null,
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
    ]);

    assertDatabaseCount(RaffleRegistration::class, 1);
});

it('does not create another registration for a duplicate normalized email', function () {
    $raffle = Raffle::factory()
        ->published()
        ->openedForParticipation(CarbonImmutable::parse('2026-07-01 09:00:00'))
        ->create();

    RaffleRegistration::factory()->for($raffle)->create([
        'name' => 'Existing Guest',
        'email' => '  ADA@Example.COM ',
    ]);

    $response = $this->withServerVariables(['HTTP_HOST' => publicParticipationHost()])
        ->post(publicParticipationUrl("/raffles/{$raffle->id}/participation"), [
            'name' => 'Another Name',
            'email' => ' ADA@EXAMPLE.COM ',
        ]);

    $response->assertRedirect(route('public.raffles.show', $raffle))
        ->assertSessionHas('public.raffles.participation_duplicate');

    assertDatabaseCount(RaffleRegistration::class, 1);

    assertDatabaseHas(RaffleRegistration::class, [
        'raffle_id' => $raffle->id,
        'name' => 'Existing Guest',
        'email' => 'ada@example.com',
    ]);
});

it('rejects submissions for a raffle that is already closed for participation', function () {
    $raffle = Raffle::factory()
        ->published()
        ->participationClosed(
            CarbonImmutable::parse('2026-07-01 09:00:00'),
            CarbonImmutable::parse('2026-07-03 21:00:00'),
        )
        ->create();

    $response = $this->withServerVariables(['HTTP_HOST' => publicParticipationHost()])
        ->post(publicParticipationUrl("/raffles/{$raffle->id}/participation"), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
        ]);

    $response->assertRedirect(route('public.raffles.show', $raffle))
        ->assertSessionHas('public.raffles.participation_unavailable');

    assertDatabaseCount(RaffleRegistration::class, 0);
});

it('revalidates eligibility server-side for stale pages before storing a registration', function () {
    $raffle = Raffle::factory()
        ->published()
        ->openedForParticipation(CarbonImmutable::parse('2026-07-01 09:00:00'))
        ->create();

    $this->withServerVariables(['HTTP_HOST' => publicParticipationHost()])
        ->get(publicParticipationUrl("/raffles/{$raffle->id}"))
        ->assertOk();

    $raffle->closeParticipation(CarbonImmutable::parse('2026-07-03 21:00:00'));

    $response = $this->withServerVariables(['HTTP_HOST' => publicParticipationHost()])
        ->post(publicParticipationUrl("/raffles/{$raffle->id}/participation"), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
        ]);

    $response->assertRedirect(route('public.raffles.show', $raffle))
        ->assertSessionHas('public.raffles.participation_unavailable');

    assertDatabaseCount(RaffleRegistration::class, 0);
});

it('returns a friendly unavailable response when a stale submit targets a raffle that is no longer public', function () {
    $raffle = Raffle::factory()
        ->published()
        ->openedForParticipation(CarbonImmutable::parse('2026-07-01 09:00:00'))
        ->create();

    $this->withServerVariables(['HTTP_HOST' => publicParticipationHost()])
        ->get(publicParticipationUrl("/raffles/{$raffle->id}"))
        ->assertOk();

    $raffle->close();

    $response = $this->withServerVariables(['HTTP_HOST' => publicParticipationHost()])
        ->post(publicParticipationUrl("/raffles/{$raffle->id}/participation"), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
        ]);

    $response->assertRedirect(route('public.home'))
        ->assertSessionHas('public.raffles.participation_unavailable');

    assertDatabaseCount(RaffleRegistration::class, 0);
});

it('preserves the public visibility boundary for direct posts to hidden raffle ids', function () {
    $raffle = Raffle::factory()->create();

    $this->withServerVariables(['HTTP_HOST' => publicParticipationHost()])
        ->post(publicParticipationUrl("/raffles/{$raffle->id}/participation"), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
        ])
        ->assertNotFound();

    assertDatabaseCount(RaffleRegistration::class, 0);
});

it('returns unavailable before validation for stale submits to raffles that became hidden', function () {
    $raffle = Raffle::factory()
        ->published()
        ->openedForParticipation(CarbonImmutable::parse('2026-07-01 09:00:00'))
        ->create();

    $this->withServerVariables(['HTTP_HOST' => publicParticipationHost()])
        ->get(publicParticipationUrl("/raffles/{$raffle->id}"))
        ->assertOk();

    $raffle->close();

    $response = $this->from(publicParticipationUrl("/raffles/{$raffle->id}"))
        ->withServerVariables(['HTTP_HOST' => publicParticipationHost()])
        ->post(publicParticipationUrl("/raffles/{$raffle->id}/participation"), [
            'name' => '',
            'email' => 'invalid-email',
        ]);

    $response->assertRedirect(route('public.home'))
        ->assertSessionHas('public.raffles.participation_unavailable')
        ->assertSessionHasNoErrors();

    assertDatabaseCount(RaffleRegistration::class, 0);
});

it('rejects invalid guest submission data without storing a registration', function () {
    $raffle = Raffle::factory()
        ->published()
        ->openedForParticipation(CarbonImmutable::parse('2026-07-01 09:00:00'))
        ->create();

    $response = $this->from(publicParticipationUrl("/raffles/{$raffle->id}"))
        ->withServerVariables(['HTTP_HOST' => publicParticipationHost()])
        ->post(publicParticipationUrl("/raffles/{$raffle->id}/participation"), [
            'name' => '',
            'email' => 'invalid-email',
        ]);

    $response->assertRedirect(publicParticipationUrl("/raffles/{$raffle->id}"))
        ->assertSessionHasErrors(['name', 'email'])
        ->assertSessionHasInput('email', 'invalid-email');

    assertDatabaseCount(RaffleRegistration::class, 0);
});
