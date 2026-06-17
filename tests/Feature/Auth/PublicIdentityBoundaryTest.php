<?php

use App\Models\Admin;
use App\Models\User;

it('documents the laravel user model as the public website identity boundary', function () {
    expect(config('auth.identity_boundary.public.model'))->toBe(User::class)
        ->and(config('auth.identity_boundary.public.table'))->toBe('users')
        ->and(config('auth.identity_boundary.public.guard'))->toBe('web')
        ->and(config('auth.identity_boundary.public.provider'))->toBe('users')
        ->and(config('auth.identity_boundary.public.passwords'))->toBe('users');
});

it('preserves user and users as the public auth contracts after admin wiring is added', function () {
    expect(config('auth.defaults.guard'))->toBe('web')
        ->and(config('auth.defaults.passwords'))->toBe('users')
        ->and(config('auth.guards.web.provider'))->toBe('users')
        ->and(config('auth.providers.users.model'))->toBe(User::class)
        ->and(config('auth.passwords.users.provider'))->toBe('users');
});

it('keeps the public provider pinned to user even if AUTH_MODEL is overridden', function () {
    $originalEnv = getenv('AUTH_MODEL');
    $hadServer = array_key_exists('AUTH_MODEL', $_SERVER);
    $originalServer = $_SERVER['AUTH_MODEL'] ?? null;
    $hadEnv = array_key_exists('AUTH_MODEL', $_ENV);
    $originalPhpEnv = $_ENV['AUTH_MODEL'] ?? null;

    putenv('AUTH_MODEL='.Admin::class);
    $_SERVER['AUTH_MODEL'] = Admin::class;
    $_ENV['AUTH_MODEL'] = Admin::class;

    try {
        $authConfig = require base_path('config/auth.php');

        expect($authConfig['providers']['users']['model'])->toBe(User::class)
            ->and($authConfig['identity_boundary']['public']['model'])->toBe(User::class);
    } finally {
        if ($originalEnv === false) {
            putenv('AUTH_MODEL');
        } else {
            putenv('AUTH_MODEL='.$originalEnv);
        }

        if ($hadServer) {
            $_SERVER['AUTH_MODEL'] = $originalServer;
        } else {
            unset($_SERVER['AUTH_MODEL']);
        }

        if ($hadEnv) {
            $_ENV['AUTH_MODEL'] = $originalPhpEnv;
        } else {
            unset($_ENV['AUTH_MODEL']);
        }
    }
});
