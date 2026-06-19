<?php

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function adminHost(): string
{
    return (string) parse_url((string) config('app.admin_url'), PHP_URL_HOST);
}

function adminUrl(string $path = '/'): string
{
    return rtrim((string) config('app.admin_url'), '/').$path;
}

it('shows the admin login page to guests on the admin host', function () {
    $this->withServerVariables(['HTTP_HOST' => adminHost()])
        ->get(adminUrl('/login'))
        ->assertOk()
        ->assertSeeText('Correo electrónico')
        ->assertSeeText('Contraseña')
        ->assertSeeText('Ingresar');
});

it('redirects guest admin html requests to the admin login page', function () {
    $this->withServerVariables(['HTTP_HOST' => adminHost()])
        ->get(adminUrl('/'))
        ->assertRedirect(route('admin.login'));
});

it('keeps json-like unauthenticated admin requests as 401 responses', function () {
    $this->withServerVariables([
        'HTTP_HOST' => adminHost(),
        'HTTP_ACCEPT' => 'application/json',
    ])->getJson(adminUrl('/'))
        ->assertUnauthorized();
});

it('creates an admin session for valid credentials and redirects to the intended page', function () {
    $admin = Admin::factory()->create();

    $this->withServerVariables(['HTTP_HOST' => adminHost()])
        ->get(adminUrl('/'));

    $response = $this->withServerVariables(['HTTP_HOST' => adminHost()])
        ->from(adminUrl('/login'))
        ->post(adminUrl('/login'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

    $response->assertRedirect(route('admin.home'));

    $this->assertAuthenticatedAs($admin, 'admin');
    $this->assertGuest('web');
});

it('rejects invalid admin credentials without creating a session', function () {
    $admin = Admin::factory()->create();

    $response = $this->withServerVariables(['HTTP_HOST' => adminHost()])
        ->from(adminUrl('/login'))
        ->post(adminUrl('/login'), [
            'email' => $admin->email,
            'password' => 'wrong-password',
        ]);

    $response->assertRedirect(adminUrl('/login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest('admin');
    $this->assertGuest('web');
});

it('logs out the admin and redirects future protected requests back to login', function () {
    $admin = Admin::factory()->create();

    $this->withServerVariables(['HTTP_HOST' => adminHost()])
        ->post(adminUrl('/login'), [
            'email' => $admin->email,
            'password' => 'password',
        ])
        ->assertRedirect(route('admin.home'));

    $this->assertAuthenticatedAs($admin, 'admin');

    $this->withServerVariables(['HTTP_HOST' => adminHost()])
        ->post(adminUrl('/logout'))
        ->assertRedirect(route('admin.login'));

    $this->assertGuest('admin');
    $this->assertGuest('web');

    $this->withServerVariables(['HTTP_HOST' => adminHost()])
        ->get(adminUrl('/'))
        ->assertRedirect(route('admin.login'));
});
