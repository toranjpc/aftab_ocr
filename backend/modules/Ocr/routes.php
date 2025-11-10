<?php

use Illuminate\Support\Facades\Route;
use Modules\Ocr\Controller\LogRepController;
use Modules\Ocr\Controller\OcrLogController;
use Modules\Ocr\Controller\OcrMatchController;
use Modules\Ocr\Controller\GateController;

// Route::post('/ocr-log', [\Modules\Traffic\Controller\TrafficController::class, 'store']);
// Route::post('/ocr-log', function () {
//     return 1;
// });
Route::post('/ocr-log', [OcrLogController::class, 'store']);
// ->middleware('auth:sanctum');//بررسی شود

Route::middleware(['auth'])->group(function () {
    Route::get('/ocr-match/list', [OcrMatchController::class, 'getList']);
    Route::get('/ocr-match/{ocr}/items', [OcrMatchController::class, 'getGroupItems']);
    Route::patch('/ocr-match/{ocrMatch}', [OcrMatchController::class, 'update']);
    Route::middleware('api')->post('/ocr-match/customCheck/{ocrMatch}', [OcrMatchController::class, 'update_customCheck']);
    Route::middleware('api')->post('/ocr-match/addBaseInvoice/{ocrMatch}', [OcrMatchController::class, 'addBaseInvoice']);
});

Route::get('/ocr-match/addBaseInvoice/54654/{ocrMatch}', function ($ocrMatch) {
    return $ocrMatches = Modules\Ocr\Models\OcrMatch::with([
        'bijacs' => function ($query) {
            $query->withCount('ocrMatches')
                ->with('invoice')
                ->with('invoiceBase')
                // ->with('allbijacs')
            ;
        },
        "isCustomCheck"
    ])
        ->whereHas('bijacs.invoicebase', null, '>=', 1)
        ->first();

    return   $ocrMatches->Invoicebaseornot;
});

Route::post('/truck-log', [OcrLogController::class, 'store2']);

Route::post('/log/rip', [LogRepController::class, 'index']);

Route::post('/check-by', [GateController::class, 'checkBy']);
