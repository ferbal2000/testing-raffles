<?php

use App\Http\Controllers\Admin\RaffleController;
use App\Models\Raffle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route as RoutingRoute;

uses(RefreshDatabase::class);

function bootAdminCloseRouteApplication(string $adminUrl): void
{
    $_ENV['ADMIN_APP_URL'] = $adminUrl;
    $_SERVER['ADMIN_APP_URL'] = $adminUrl;
    putenv("ADMIN_APP_URL={$adminUrl}");
    test()->refreshApplication();
}

function adminCloseRouteDefinition(): ?RoutingRoute
{
    return collect(app('router')->getRoutes()->getRoutes())
        ->first(fn (RoutingRoute $route): bool => $route->getName() === 'admin.raffles.close');
}

afterEach(function () {
    $_ENV['ADMIN_APP_URL'] = 'http://admin.raffles.test';
    $_SERVER['ADMIN_APP_URL'] = 'http://admin.raffles.test';
    putenv('ADMIN_APP_URL=http://admin.raffles.test');
});

it('registers the protected close command after a fresh configured-host or fallback application boot', function (
    string $adminUrl,
    ?string $expectedDomain,
) {
    bootAdminCloseRouteApplication($adminUrl);

    $route = adminCloseRouteDefinition();

    expect($route)->not->toBeNull()
        ->and($route?->methods())->toContain('POST')
        ->and($route?->getActionName())->toBe(RaffleController::class.'@close')
        ->and($route?->gatherMiddleware())->toContain('auth:admin')
        ->and($route?->getDomain())->toBe($expectedDomain);
})->with([
    'configured admin host' => ['http://admin.raffles.test', 'admin.raffles.test'],
    'fallback routes' => ['/', null],
]);

it('keeps guest html and json close submissions behind admin authentication in both route modes', function (
    string $adminUrl,
    string $requestUrl,
    int $htmlStatus,
) {
    bootAdminCloseRouteApplication($adminUrl);
    $raffleCount = Raffle::query()->count();

    $htmlResponse = $this->post("{$requestUrl}/raffles/999999/close");
    $jsonResponse = $this->postJson("{$requestUrl}/raffles/999999/close");

    if ($htmlStatus === 302) {
        $htmlResponse->assertRedirect(route('admin.login'));
    } else {
        $htmlResponse->assertStatus($htmlStatus);
    }

    $jsonResponse->assertUnauthorized();

    expect(Raffle::query()->count())->toBe($raffleCount);
})->with([
    'configured admin host' => ['http://admin.raffles.test', 'http://admin.raffles.test', 302],
    'fallback routes' => ['/', 'http://fallback-admin.test', 401],
]);
