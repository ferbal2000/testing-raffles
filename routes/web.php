<?php

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

$publicHost = parse_url((string) config('app.public_url'), PHP_URL_HOST);

$publicBoundaryProbePayload = function (Request $request): array {
    $user = Auth::guard('web')->user();

    return [
        'boundary' => $request->attributes->get('identity_boundary'),
        'guard' => 'web',
        'authenticated' => Auth::guard('web')->check(),
        'via_remember' => Auth::guard('web')->viaRemember(),
        'session_cookie' => config('session.cookie'),
        'user_id' => $user?->getAuthIdentifier(),
        'user_type' => $user ? class_basename($user) : null,
    ];
};

if (is_string($publicHost) && $publicHost !== '') {
    Route::domain($publicHost)->group(function () use ($publicBoundaryProbePayload): void {
        Route::view('/', 'public.home')->name('public.home');

        Route::get('/_test/auth/public/login/{user}', function (Request $request, User $user) use ($publicBoundaryProbePayload): JsonResponse {
            abort_unless(app()->runningInConsole(), 404);

            Auth::guard('web')->login($user, $request->boolean('remember'));

            return response()->json($publicBoundaryProbePayload($request));
        });

        Route::get('/_test/auth/probe', function (Request $request) use ($publicBoundaryProbePayload): JsonResponse {
            abort_unless(app()->runningInConsole(), 404);

            return response()->json($publicBoundaryProbePayload($request));
        });
    });
} else {
    Route::view('/', 'public.home')->name('public.home');
}
