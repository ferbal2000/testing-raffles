<?php

use Illuminate\Support\Facades\Route;

$publicHost = parse_url((string) config('app.public_url'), PHP_URL_HOST);

if (is_string($publicHost) && $publicHost !== '') {
    Route::domain($publicHost)->group(function (): void {
        Route::view('/', 'public.home')->name('public.home');
    });
} else {
    Route::view('/', 'public.home')->name('public.home');
}
