<?php

use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

$adminHost = parse_url((string) config('app.admin_url'), PHP_URL_HOST);

$adminBoundaryProbePayload = function (Request $request): array {
    $user = Auth::guard('admin')->user();

    return [
        'boundary' => $request->attributes->get('identity_boundary'),
        'guard' => 'admin',
        'authenticated' => Auth::guard('admin')->check(),
        'via_remember' => Auth::guard('admin')->viaRemember(),
        'session_cookie' => config('session.cookie'),
        'user_id' => $user?->getAuthIdentifier(),
        'user_type' => $user ? class_basename($user) : null,
    ];
};

if (is_string($adminHost) && $adminHost !== '') {
    Route::domain($adminHost)->group(function () use ($adminBoundaryProbePayload): void {
        Route::middleware('guest:admin')->group(function (): void {
            Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('admin.login');
            Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('admin.login.store');
        });

        Route::middleware('auth:admin')->group(function (): void {
            Route::view('/', 'admin.home')->name('admin.home');
            Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('admin.logout');
        });

        Route::get('/_test/auth/admin/login/{admin}', function (Request $request, Admin $admin) use ($adminBoundaryProbePayload): JsonResponse {
            abort_unless(app()->runningInConsole(), 404);

            Auth::guard('admin')->login($admin, $request->boolean('remember'));

            return response()->json($adminBoundaryProbePayload($request));
        });

        Route::get('/_test/auth/probe', function (Request $request) use ($adminBoundaryProbePayload): JsonResponse {
            abort_unless(app()->runningInConsole(), 404);

            return response()->json($adminBoundaryProbePayload($request));
        });
    });
} else {
    Route::middleware('guest:admin')->group(function (): void {
        Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('admin.login');
        Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('admin.login.store');
    });

    Route::middleware('auth:admin')->group(function (): void {
        Route::view('/', 'admin.home')->name('admin.home');
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('admin.logout');
    });
}
