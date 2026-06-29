<?php

use App\Models\Raffle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Lang;

uses(RefreshDatabase::class);

function publicDetailTranslationHost(): string
{
    return (string) parse_url((string) config('app.public_url'), PHP_URL_HOST);
}

function publicDetailTranslationUrl(string $path = ''): string
{
    return rtrim((string) config('app.public_url'), '/').$path;
}

beforeEach(function () {
    app()->setLocale('es');
});

it('renders the public raffle detail copy from translation keys without raw enum values', function () {
    $raffle = Raffle::factory()->published()->create();

    Lang::addLines([
        'public-raffles.title' => 'Detalle público traducido',
        'public-raffles.status_label' => 'Estado traducido',
        'public-raffles.status.published' => 'Sorteo publicado para el público',
        'public-raffles.availability_label' => 'Disponibilidad traducida',
        'public-raffles.availability.closed' => 'Participación cerrada por traducción',
    ], 'es');

    $this->withServerVariables(['HTTP_HOST' => publicDetailTranslationHost()])
        ->get(publicDetailTranslationUrl("/raffles/{$raffle->id}"))
        ->assertOk()
        ->assertSeeText('Detalle público traducido')
        ->assertSeeText('Estado traducido')
        ->assertSeeText('Sorteo publicado para el público')
        ->assertSeeText('Disponibilidad traducida')
        ->assertSeeText('Participación cerrada por traducción')
        ->assertDontSeeText('published');
});
