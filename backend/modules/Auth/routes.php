<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Controllers\AuthController;
use Modules\Auth\Controllers\AIAuthController;

Route::get('captcha', function () {
    return [app('Captcha')::create('flat', true)];
});

Route::post('login', [AuthController::class, 'login'])->name('login');

Route::middleware(['auth'])->group(function () {
    Route::post('change-pass', [AuthController::class, 'changePass']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);
});
