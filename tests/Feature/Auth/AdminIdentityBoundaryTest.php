<?php

use App\Http\Middleware\ApplyIdentityBoundary;
use App\Http\Middleware\ApplyIdentityBoundarySessionCookie;
use App\Models\Admin;
use Illuminate\Contracts\Http\Kernel;

it('uses the admin model and admins table for the admin identity boundary', function () {
    expect(config('auth.identity_boundary.admin.model'))->toBe(Admin::class)
        ->and(config('auth.identity_boundary.admin.table'))->toBe('admins')
        ->and(config('auth.identity_boundary.admin.guard'))->toBe('admin')
        ->and(config('auth.identity_boundary.admin.provider'))->toBe('admins')
        ->and(config('auth.identity_boundary.admin.passwords'))->toBe('admins');
});

it('wires the admin guard and provider explicitly', function () {
    expect(config('auth.guards.admin.driver'))->toBe('session')
        ->and(config('auth.guards.admin.provider'))->toBe('admins')
        ->and(config('auth.providers.admins.driver'))->toBe('eloquent')
        ->and(config('auth.providers.admins.model'))->toBe(Admin::class);
});

it('isolates admin password recovery through the admins broker only', function () {
    expect(config('auth.passwords.admins.provider'))->toBe('admins')
        ->and(config('auth.passwords.admins.table'))->toBe('admin_password_reset_tokens')
        ->and(config('auth.passwords.users.provider'))->toBe('users')
        ->and(config('auth.passwords.users.table'))->toBe('password_reset_tokens');
});

it('classifies the boundary and applies the session cookie from global middleware before routing', function () {
    $middleware = app(Kernel::class)->getGlobalMiddleware();

    expect(array_search(ApplyIdentityBoundary::class, $middleware, true))->toBeInt()
        ->and(array_search(ApplyIdentityBoundarySessionCookie::class, $middleware, true))->toBeInt()
        ->and(array_search(ApplyIdentityBoundary::class, $middleware, true))
        ->toBeLessThan(array_search(ApplyIdentityBoundarySessionCookie::class, $middleware, true));
});
