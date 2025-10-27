<?php

use Illuminate\Support\Facades\Route;
use Modules\Traffic\Controller\LogRepController;
use Modules\Traffic\Controller\TrafficController;
use Modules\Traffic\Controller\TrafficMatchController;
use Modules\Traffic\Controller\GateController;

Route::post('/traffic/addlog', [TrafficController::class, 'store']);
    // ->middleware('auth:sanctum');//بررسی شود

// Route::middleware(['auth'])->group(function () {
//     Route::get('/traffic-match/list', [TrafficMatchController::class, 'getList']);
//     Route::get('/traffic-match/{traffic}/items', [TrafficMatchController::class, 'getGroupItems']);
//     Route::patch('/traffic-match/{trafficMatch}', [TrafficMatchController::class, 'update']);
//     Route::middleware('api')->post('/traffic-match/customCheck/{trafficMatch}', [TrafficMatchController::class, 'update_customCheck']);
// });

// Route::post('/truck-log', [TrafficController::class, 'store2']);

// Route::post('/log/rip', [LogRepController::class, 'index']);

// Route::post('/check-by', [GateController::class, 'checkBy']);
