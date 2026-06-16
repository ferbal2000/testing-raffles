<?php

use App\Models\User;

it('documents the laravel user model as the public website identity boundary', function () {
    expect(config('auth.identity_boundary.public.model'))->toBe(User::class)
        ->and(config('auth.identity_boundary.public.table'))->toBe('users')
        ->and(config('auth.identity_boundary.public.guard'))->toBe('web')
        ->and(config('auth.identity_boundary.public.provider'))->toBe('users');
});

it('documents admin identity as a separate later slice concern', function () {
    expect(config('auth.identity_boundary.admin.status'))->toBe('planned')
        ->and(config('auth.identity_boundary.admin.future_boundary'))->toBe('separate-model-table-guard')
        ->and(config('auth.identity_boundary.admin.implemented_in_this_slice'))->toBeFalse();
});
