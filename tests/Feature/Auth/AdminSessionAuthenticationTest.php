<?php

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

function adminHost(): string
{
    return (string) parse_url((string) config('app.admin_url'), PHP_URL_HOST);
}

function adminUrl(string $path = '/'): string
{
    return rtrim((string) config('app.admin_url'), '/').$path;
}

function adminResponseCookies(TestResponse $response): array
{
    $cookies = [];

    foreach ($response->headers->getCookies() as $cookie) {
        $cookies[$cookie->getName()] = $cookie->getValue();
    }

    return $cookies;
}

function mergedAdminBrowserCookies(array $cookies, TestResponse $response): array
{
    return array_merge($cookies, adminResponseCookies($response));
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

it('keeps the admin browser login flow on the admin session cookie across redirects', function () {
    $admin = Admin::factory()->create();

    $loginPageResponse = $this->withServerVariables(['HTTP_HOST' => adminHost()])
        ->get(adminUrl('/login'));

    $loginPageResponse->assertOk();

    $browserCookies = adminResponseCookies($loginPageResponse);

    expect($browserCookies)
        ->toHaveKey(config('session.identity_boundary.cookies.admin'))
        ->not->toHaveKey(config('session.identity_boundary.cookies.public'));

    $loginResponse = $this->withServerVariables(['HTTP_HOST' => adminHost()])
        ->withCookies($browserCookies)
        ->from(adminUrl('/login'))
        ->post(adminUrl('/login'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

    $loginResponse->assertRedirect(route('admin.home'));

    $browserCookies = mergedAdminBrowserCookies($browserCookies, $loginResponse);

    expect($browserCookies)
        ->toHaveKey(config('session.identity_boundary.cookies.admin'))
        ->not->toHaveKey(config('session.identity_boundary.cookies.public'));

    $this->withServerVariables(['HTTP_HOST' => adminHost()])
        ->withCookies($browserCookies)
        ->get(adminUrl('/'))
        ->assertOk();
});

it('keeps admin auth on the admin session cookie even if the session store resolved early', function () {
    $admin = Admin::factory()->create();

    app('session.store');

    $loginPageResponse = $this->withServerVariables(['HTTP_HOST' => adminHost()])
        ->get(adminUrl('/login'));

    $loginPageResponse->assertOk();

    $browserCookies = adminResponseCookies($loginPageResponse);

    expect($browserCookies)
        ->toHaveKey(config('session.identity_boundary.cookies.admin'))
        ->not->toHaveKey(config('session.identity_boundary.cookies.public'));

    $loginResponse = $this->withServerVariables(['HTTP_HOST' => adminHost()])
        ->withCookies($browserCookies)
        ->from(adminUrl('/login'))
        ->post(adminUrl('/login'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

    $loginResponse->assertRedirect(route('admin.home'));

    $browserCookies = mergedAdminBrowserCookies($browserCookies, $loginResponse);

    expect($browserCookies)
        ->toHaveKey(config('session.identity_boundary.cookies.admin'))
        ->not->toHaveKey(config('session.identity_boundary.cookies.public'));

    $this->withServerVariables(['HTTP_HOST' => adminHost()])
        ->withCookies($browserCookies)
        ->get(adminUrl('/'))
        ->assertOk();
});

it('accepts a stale public session cookie without falling into an admin redirect loop', function () {
    $admin = Admin::factory()->create();
    $user = User::factory()->create();

    $publicCookieResponse = $this->withServerVariables([
        'HTTP_HOST' => (string) parse_url((string) config('app.public_url'), PHP_URL_HOST),
    ])->get(rtrim((string) config('app.public_url'), '/')."/_test/auth/public/login/{$user->getKey()}");

    $publicCookieResponse->assertOk();

    $browserCookies = adminResponseCookies($publicCookieResponse);

    expect($browserCookies)->toHaveKey(config('session.identity_boundary.cookies.public'));

    $loginPageResponse = $this->withServerVariables(['HTTP_HOST' => adminHost()])
        ->withCookies($browserCookies)
        ->get(adminUrl('/login'));

    $loginPageResponse->assertOk();

    $browserCookies = mergedAdminBrowserCookies($browserCookies, $loginPageResponse);

    $loginResponse = $this->withServerVariables(['HTTP_HOST' => adminHost()])
        ->withCookies($browserCookies)
        ->from(adminUrl('/login'))
        ->post(adminUrl('/login'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

    $loginResponse->assertRedirect(route('admin.home'));

    $browserCookies = mergedAdminBrowserCookies($browserCookies, $loginResponse);

    $this->withServerVariables(['HTTP_HOST' => adminHost()])
        ->withCookies($browserCookies)
        ->get(adminUrl('/'))
        ->assertOk();
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
