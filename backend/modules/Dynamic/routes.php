<?php
use Illuminate\Support\Facades\Route;
use Modules\Dynamic\Controllers\DynamicController;
use Modules\Dynamic\Controllers\FilterController;

Route::group(['middleware' => ['auth:api']], function () {
    Route::post('filter', [FilterController::class, 'filter']);
    Route::post('sum-model', [DynamicController::class, 'sum']);
    Route::get('{modelName}', [DynamicController::class, 'index']);
    Route::post('{modelName}', [DynamicController::class, 'store']);
    Route::patch('{modelName}/{id}', [DynamicController::class, 'update']);
    Route::get('{modelName}/{id}', [DynamicController::class, 'show']);
    Route::delete('{modelName}/{id}', [DynamicController::class, 'destroy']);
});
