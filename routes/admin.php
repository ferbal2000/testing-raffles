<?php

use Illuminate\Support\Facades\Route;

$adminHost = parse_url((string) config('app.admin_url'), PHP_URL_HOST);

if (is_string($adminHost) && $adminHost !== '') {
    Route::domain($adminHost)->group(function (): void {
        Route::view('/', 'admin.home')->name('admin.home');
    });
} else {
    Route::view('/', 'admin.home')->name('admin.home');
}
