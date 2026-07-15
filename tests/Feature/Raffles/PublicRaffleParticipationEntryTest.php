<?php

use App\Enums\RaffleRegistrationStatus;
use App\Models\Admin;
use App\Models\Raffle;
use App\Models\RaffleRegistration;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

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

function assertExplicitRegistrationStatusPersists(RaffleRegistrationStatus $status): void
{
    $raffle = Raffle::factory()
        ->published()
        ->openedForParticipation(CarbonImmutable::parse('2026-07-01 09:00:00'))
        ->create();

    $registration = RaffleRegistration::query()->create([
        'raffle_id' => $raffle->id,
        'user_id' => null,
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'status' => $status,
    ]);

    $freshRegistration = $registration->fresh();

    expect($freshRegistration->status)->toBe($status);

    assertDatabaseHas(RaffleRegistration::class, [
        'raffle_id' => $raffle->id,
        'email' => 'ada@example.com',
        'status' => $status->value,
    ]);
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

it('persists an explicit flagged registration status through the model boundary', function () {
    assertExplicitRegistrationStatusPersists(RaffleRegistrationStatus::Flagged);
});

it('persists an explicit cancelled registration status through the model boundary', function () {
    assertExplicitRegistrationStatusPersists(RaffleRegistrationStatus::Cancelled);
});

it('rejects unsupported registration statuses before storing them', function () {
    $raffle = Raffle::factory()
        ->published()
        ->openedForParticipation(CarbonImmutable::parse('2026-07-01 09:00:00'))
        ->create();

    expect(fn () => RaffleRegistration::query()->create([
        'raffle_id' => $raffle->id,
        'user_id' => null,
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'status' => 'pending',
    ]))->toThrow(ValueError::class);

    assertDatabaseCount(RaffleRegistration::class, 0);
});

it('treats registrations without an explicit status as active at the storage boundary', function () {
    $raffle = Raffle::factory()
        ->published()
        ->openedForParticipation(CarbonImmutable::parse('2026-07-01 09:00:00'))
        ->create();

    $registrationId = DB::table('raffle_registrations')->insertGetId([
        'raffle_id' => $raffle->id,
        'user_id' => null,
        'name' => 'Existing Guest',
        'email' => 'existing@example.com',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $registration = RaffleRegistration::query()->findOrFail($registrationId);

    expect($registration->status)->toBe(RaffleRegistrationStatus::Active);

    assertDatabaseHas(RaffleRegistration::class, [
        'raffle_id' => $raffle->id,
        'email' => 'existing@example.com',
        'status' => RaffleRegistrationStatus::Active->value,
    ]);
});

it('accepts an eligible guest submission and stores a normalized registration', function () {
    $raffle = Raffle::factory()
        ->published()
        ->openedForParticipation(CarbonImmutable::parse('2026-07-01 09:00:00'))
        ->create();

    $this->followingRedirects()
        ->withServerVariables(['HTTP_HOST' => publicParticipationHost()])
        ->post(publicParticipationUrl("/raffles/{$raffle->id}/participation"), [
            'name' => 'Ada Lovelace',
            'email' => '  ADA@Example.COM ',
        ])
        ->assertOk()
        ->assertSeeText('Tu participación quedó registrada.');

    assertDatabaseHas(RaffleRegistration::class, [
        'raffle_id' => $raffle->id,
        'user_id' => null,
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'status' => RaffleRegistrationStatus::Active->value,
    ]);

    expect(RaffleRegistration::query()->firstOrFail()->status)->toBe(RaffleRegistrationStatus::Active);

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

    $this->followingRedirects()
        ->withServerVariables(['HTTP_HOST' => publicParticipationHost()])
        ->post(publicParticipationUrl("/raffles/{$raffle->id}/participation"), [
            'name' => 'Another Name',
            'email' => ' ADA@EXAMPLE.COM ',
        ])
        ->assertOk()
        ->assertSeeText('Ese correo ya estaba registrado para este sorteo.');

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

    $this->followingRedirects()
        ->withServerVariables(['HTTP_HOST' => publicParticipationHost()])
        ->post(publicParticipationUrl("/raffles/{$raffle->id}/participation"), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
        ])
        ->assertOk()
        ->assertSeeText('La participación no está disponible en este momento.')
        ->assertSeeText('La inscripción está cerrada por ahora.')
        ->assertDontSee('name="name"', false);

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

it('rejects a stale public submission after the admin close endpoint freezes participation', function () {
    $admin = Admin::factory()->create();
    $raffle = Raffle::factory()
        ->published()
        ->openedForParticipation(CarbonImmutable::parse('2026-07-01 09:00:00'))
        ->create();

    $this->withServerVariables(['HTTP_HOST' => publicParticipationHost()])
        ->get(publicParticipationUrl("/raffles/{$raffle->id}"))
        ->assertOk();

    $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => adminRaffleHost()])
        ->post(adminRaffleUrl("/raffles/{$raffle->id}/close"))
        ->assertRedirect(route('admin.raffles.index'))
        ->assertSessionHas('admin.raffles.close_success');

    $this->withServerVariables(['HTTP_HOST' => publicParticipationHost()])
        ->post(publicParticipationUrl("/raffles/{$raffle->id}/participation"), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
        ])
        ->assertRedirect(route('public.home'))
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

    $raffle->close(CarbonImmutable::now(), 'raffle_closed', null);

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

    $raffle->close(CarbonImmutable::now(), 'raffle_closed', null);

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

it('shows unavailable feedback on the public catalog when a stale submit targets a raffle that became hidden', function () {
    $raffle = Raffle::factory()
        ->published()
        ->openedForParticipation(CarbonImmutable::parse('2026-07-01 09:00:00'))
        ->create();

    $this->withServerVariables(['HTTP_HOST' => publicParticipationHost()])
        ->get(publicParticipationUrl("/raffles/{$raffle->id}"))
        ->assertOk();

    $raffle->close(CarbonImmutable::now(), 'raffle_closed', null);

    $this->followingRedirects()
        ->withServerVariables(['HTTP_HOST' => publicParticipationHost()])
        ->post(publicParticipationUrl("/raffles/{$raffle->id}/participation"), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
        ])
        ->assertOk()
        ->assertSeeText('Catálogo público')
        ->assertSeeText('La participación no está disponible en este momento.');

    assertDatabaseCount(RaffleRegistration::class, 0);
});

it('rejects invalid guest submission data without storing a registration', function () {
    $raffle = Raffle::factory()
        ->published()
        ->openedForParticipation(CarbonImmutable::parse('2026-07-01 09:00:00'))
        ->create();

    $this->from(publicParticipationUrl("/raffles/{$raffle->id}"))
        ->followingRedirects()
        ->withServerVariables(['HTTP_HOST' => publicParticipationHost()])
        ->post(publicParticipationUrl("/raffles/{$raffle->id}/participation"), [
            'name' => '',
            'email' => 'invalid-email',
        ])
        ->assertOk()
        ->assertSeeText('Revisá los datos del formulario e intentá de nuevo.')
        ->assertSee('value="invalid-email"', false);

    assertDatabaseCount(RaffleRegistration::class, 0);
});
