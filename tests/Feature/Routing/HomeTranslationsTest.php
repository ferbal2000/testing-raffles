<?php

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Lang;

uses(RefreshDatabase::class);

function translationHostFor(string $configKey): string
{
    return (string) parse_url((string) config($configKey), PHP_URL_HOST);
}

function translationUrlFor(string $configKey): string
{
    return (string) config($configKey);
}

beforeEach(function () {
    app()->setLocale('es');
});

it('renders the public home copy from translation keys', function () {
    Lang::addLines([
        'home.public.title' => 'Título público de prueba',
        'home.public.description' => 'Descripción pública de prueba',
    ], 'es');

    $this->withServerVariables(['HTTP_HOST' => translationHostFor('app.public_url')])
        ->get(translationUrlFor('app.public_url'))
        ->assertOk()
        ->assertSeeText('Título público de prueba')
        ->assertSeeText('Descripción pública de prueba')
        ->assertDontSee('/raffles/')
        ->assertDontSee('href="/raffles/', false)
        ->assertDontSeeText('Participá en sorteos transparentes');
});

it('renders the admin home copy from translation keys for authenticated admins', function () {
    $admin = Admin::factory()->create();

    Lang::addLines([
        'home.admin.title' => 'Título admin de prueba',
        'home.admin.description' => 'Descripción admin de prueba',
    ], 'es');

    $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => translationHostFor('app.admin_url')])
        ->get(translationUrlFor('app.admin_url'))
        ->assertOk()
        ->assertSeeText('Título admin de prueba')
        ->assertSeeText('Descripción admin de prueba')
        ->assertDontSeeText('Administración de sorteos');
});
