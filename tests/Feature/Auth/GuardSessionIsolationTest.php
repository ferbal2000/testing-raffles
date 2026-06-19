<?php

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

function boundaryHost(string $configKey): string
{
    return (string) parse_url((string) config($configKey), PHP_URL_HOST);
}

function boundaryUrl(string $configKey, string $path): string
{
    return rtrim((string) config($configKey), '/').$path;
}

function responseCookies(TestResponse $response): array
{
    $cookies = [];

    foreach ($response->headers->getCookies() as $cookie) {
        $cookies[$cookie->getName()] = $cookie->getValue();
    }

    return $cookies;
}

function mergedBrowserCookies(array $cookies, TestResponse $response): array
{
    return array_merge($cookies, responseCookies($response));
}

function rememberCookie(array $cookies, string $guard): array
{
    $prefix = "remember_{$guard}_";

    foreach ($cookies as $name => $value) {
        if (str_starts_with($name, $prefix)) {
            return [$name => $value];
        }
    }

    return [];
}

it('keeps admin auth cookies isolated from the public boundary across hosts', function () {
    $admin = Admin::factory()->create();

    $loginResponse = $this->withServerVariables(['HTTP_HOST' => boundaryHost('app.admin_url')])
        ->get(boundaryUrl('app.admin_url', "/_test/auth/admin/login/{$admin->getKey()}?remember=1"));

    $loginResponse->assertOk()
        ->assertJsonPath('boundary', 'admin')
        ->assertJsonPath('guard', 'admin')
        ->assertJsonPath('authenticated', true)
        ->assertJsonPath('session_cookie', config('session.identity_boundary.cookies.admin'));

    $adminCookies = responseCookies($loginResponse);

    expect($adminCookies)->toHaveKey(config('session.identity_boundary.cookies.admin'))
        ->and($adminCookies)->not->toHaveKey(config('session.identity_boundary.cookies.public'))
        ->and(rememberCookie($adminCookies, 'admin'))->not->toBe([]);

    $publicProbe = $this->withServerVariables(['HTTP_HOST' => boundaryHost('app.public_url')])
        ->withCookies($adminCookies)
        ->get(boundaryUrl('app.public_url', '/_test/auth/probe'));

    $publicProbe->assertOk()
        ->assertJsonPath('boundary', 'public')
        ->assertJsonPath('guard', 'web')
        ->assertJsonPath('authenticated', false)
        ->assertJsonPath('via_remember', false)
        ->assertJsonPath('session_cookie', config('session.identity_boundary.cookies.public'));
});

it('does not treat public remember me state as admin authentication', function () {
    $user = User::factory()->create();

    $loginResponse = $this->withServerVariables(['HTTP_HOST' => boundaryHost('app.public_url')])
        ->get(boundaryUrl('app.public_url', "/_test/auth/public/login/{$user->getKey()}?remember=1"));

    $loginResponse->assertOk()
        ->assertJsonPath('boundary', 'public')
        ->assertJsonPath('guard', 'web')
        ->assertJsonPath('authenticated', true)
        ->assertJsonPath('session_cookie', config('session.identity_boundary.cookies.public'));

    $rememberOnlyCookies = rememberCookie(responseCookies($loginResponse), 'web');

    expect($rememberOnlyCookies)->not->toBe([])
        ->and($rememberOnlyCookies)->not->toHaveKey(config('session.identity_boundary.cookies.public'));

    $publicProbe = $this->withServerVariables(['HTTP_HOST' => boundaryHost('app.public_url')])
        ->withCookies($rememberOnlyCookies)
        ->get(boundaryUrl('app.public_url', '/_test/auth/probe'));

    $publicProbe->assertOk()
        ->assertJsonPath('boundary', 'public')
        ->assertJsonPath('guard', 'web')
        ->assertJsonPath('authenticated', true);

    $adminProbe = $this->withServerVariables(['HTTP_HOST' => boundaryHost('app.admin_url')])
        ->withCookies($rememberOnlyCookies)
        ->get(boundaryUrl('app.admin_url', '/_test/auth/probe'));

    $adminProbe->assertOk()
        ->assertJsonPath('boundary', 'admin')
        ->assertJsonPath('guard', 'admin')
        ->assertJsonPath('authenticated', false)
        ->assertJsonPath('via_remember', false)
        ->assertJsonPath('session_cookie', config('session.identity_boundary.cookies.admin'));
});

it('requires guard and session assertions instead of trusting host routing alone', function () {
    $user = User::factory()->create();

    $loginResponse = $this->withServerVariables(['HTTP_HOST' => boundaryHost('app.public_url')])
        ->get(boundaryUrl('app.public_url', "/_test/auth/public/login/{$user->getKey()}"));

    $publicCookies = responseCookies($loginResponse);

    expect($publicCookies)->toHaveKey(config('session.identity_boundary.cookies.public'))
        ->and($publicCookies)->not->toHaveKey(config('session.identity_boundary.cookies.admin'));

    $publicProbe = $this->withServerVariables(['HTTP_HOST' => boundaryHost('app.public_url')])
        ->withCookies($publicCookies)
        ->get(boundaryUrl('app.public_url', '/_test/auth/probe'));

    $publicProbe->assertOk()
        ->assertJsonPath('boundary', 'public')
        ->assertJsonPath('authenticated', true)
        ->assertJsonPath('session_cookie', config('session.identity_boundary.cookies.public'));

    $adminProbe = $this->withServerVariables(['HTTP_HOST' => boundaryHost('app.admin_url')])
        ->withCookies($publicCookies)
        ->get(boundaryUrl('app.admin_url', '/_test/auth/probe'));

    $adminProbe->assertOk()
        ->assertJsonPath('boundary', 'admin')
        ->assertJsonPath('guard', 'admin')
        ->assertJsonPath('authenticated', false)
        ->assertJsonPath('session_cookie', config('session.identity_boundary.cookies.admin'));
});

it('keeps the public boundary unauthenticated before and after the admin login lifecycle', function () {
    $admin = Admin::factory()->create();

    $publicProbeBefore = $this->withServerVariables(['HTTP_HOST' => boundaryHost('app.public_url')])
        ->get(boundaryUrl('app.public_url', '/_test/auth/probe'));

    $publicProbeBefore->assertOk()
        ->assertJsonPath('boundary', 'public')
        ->assertJsonPath('guard', 'web')
        ->assertJsonPath('authenticated', false);

    $loginResponse = $this->withServerVariables(['HTTP_HOST' => boundaryHost('app.admin_url')])
        ->post(boundaryUrl('app.admin_url', '/login'), [
            'email' => $admin->email,
            'password' => 'password',
            'remember' => '1',
        ]);

    $loginResponse->assertRedirect(route('admin.home'));

    $adminBrowserCookies = responseCookies($loginResponse);

    expect(rememberCookie($adminBrowserCookies, 'admin'))->not->toBe([]);

    $this->assertAuthenticatedAs($admin, 'admin');
    $this->assertGuest('web');

    $publicProbeAfterLogin = $this->withServerVariables(['HTTP_HOST' => boundaryHost('app.public_url')])
        ->withCookies($adminBrowserCookies)
        ->get(boundaryUrl('app.public_url', '/_test/auth/probe'));

    $publicProbeAfterLogin->assertOk()
        ->assertJsonPath('boundary', 'public')
        ->assertJsonPath('guard', 'web')
        ->assertJsonPath('authenticated', false);

    $logoutResponse = $this->withServerVariables(['HTTP_HOST' => boundaryHost('app.admin_url')])
        ->withCookies($adminBrowserCookies)
        ->post(boundaryUrl('app.admin_url', '/logout'));

    $logoutResponse->assertRedirect(route('admin.login'));

    $browserCookiesAfterLogout = mergedBrowserCookies($adminBrowserCookies, $logoutResponse);

    $this->assertGuest('admin');
    $this->assertGuest('web');

    $publicProbeAfterLogout = $this->withServerVariables(['HTTP_HOST' => boundaryHost('app.public_url')])
        ->withCookies($browserCookiesAfterLogout)
        ->get(boundaryUrl('app.public_url', '/_test/auth/probe'));

    $publicProbeAfterLogout->assertOk()
        ->assertJsonPath('boundary', 'public')
        ->assertJsonPath('guard', 'web')
        ->assertJsonPath('authenticated', false);
});
