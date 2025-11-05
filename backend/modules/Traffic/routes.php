<?php

use Illuminate\Support\Facades\Route;
use Modules\Traffic\Controller\LogRepController;
use Modules\Traffic\Controller\TrafficController;
use Modules\Traffic\Controller\TrafficMatchController;
use Modules\Traffic\Controller\GateController;
use Illuminate\Support\Facades\Cache;


Route::get('/sse/traffic/test/add', function () {
    Cache::put('traffic_data', [
        ['id' => 1, 'plate' => '21الف123', 'status' => 'OK', 'time' => now()->toDateTimeString()],
        ['id' => 2, 'plate' => '45ب456', 'status' => 'Processing', 'time' => now()->toDateTimeString()],
    ], 3600);
});

Route::get('/sse/traffic/test', function () {
    return response()->stream(function () {
        while (true) {
            $data = Cache::pull('traffic_data');
            if (!$data) $data = ['status' => 'empty'];

            echo "data: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";
            ob_flush();
            flush();

            sleep(1);
        }
    }, 200, [
        'Content-Type'  => 'text/event-stream',
        'Cache-Control' => 'no-cache',
        'Connection'    => 'keep-alive',
    ]);
});

/*
Route::post('/traffic/addlog', [TrafficController::class, 'store']);
// ->middleware('auth:sanctum');//بررسی شود

Route::middleware(['auth'])->group(function () {
    Route::get('/traffics/bygate/list/all', [TrafficController::class, 'getList'])->name('traffic_list');
    //     Route::get('/traffic-match/{traffic}/items', [TrafficMatchController::class, 'getGroupItems']);
    //     Route::patch('/traffic-match/{trafficMatch}', [TrafficMatchController::class, 'update']);
    Route::middleware('api')->post('/traffic-match/customCheck/{trafficMatch}', [TrafficController::class, 'update_customCheck']);
});

// Route::post('/truck-log', [TrafficController::class, 'store2']);

// Route::post('/log/rip', [LogRepController::class, 'index']);

// Route::post('/check-by', [GateController::class, 'checkBy']);
*/