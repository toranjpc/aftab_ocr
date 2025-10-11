<?php
use Illuminate\Support\Facades\Route;
use Modules\Sse\Controllers\SSEController;

Route::get('sse/{event}', [SSEController::class, 'sse'])
->middleware('auth');
