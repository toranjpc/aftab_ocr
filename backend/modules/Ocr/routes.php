<?php

use Illuminate\Support\Facades\Route;
use Modules\Ocr\Controller\LogRepController;
use Modules\Ocr\Controller\OcrLogController;
use Modules\Ocr\Controller\OcrMatchController;
use Modules\Ocr\Controller\GateController;
use Modules\BijacInvoice\Models\Invoice;
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
    // return Invoice::selectRaw('id , receipt_number , base')->where("receipt_number", "BSRCC14040169603")->get();
    // return $aaa = Invoice::selectRaw('id , receipt_number , count(id) as count')
    //     ->with("bijacs")
    //     ->orderBy('id', 'desc') // یا created_at
    //     ->groupBy('receipt_number')
    //     ->havingRaw('COUNT(*) > 1')
    //     ->limit(10)
    //     ->get();

    $ocrMatches = Modules\Ocr\Models\OcrMatch::where('plate_number', $ocrMatch)
        // ->with([
        //     'bijacs' => function ($query) {
        //         $query->withCount('ocrMatches')
        //             ->with('invoices')
        //             ->with('invoice')
        //             ->with('invoiceBase')
        //             ->with('allbijacs')
        //         ;
        //     },
        // "isCustomCheck"
        // ])
        ->first();
    $Invoicebaseornot = !empty($ocrMatches->invoicebaseornot) ? "invoicebaseornot" : "invoice";

    $ocrMatches->append($Invoicebaseornot);

    return $ocrMatches; //->invoicebaseornot;
});

Route::post('/truck-log', [OcrLogController::class, 'store2']);

Route::post('/log/rip', [LogRepController::class, 'index']);
Route::post('/log/gateCounter', [LogRepController::class, 'gateCounter']);

Route::post('/check-by', [GateController::class, 'checkBy']);
Route::post('/findAftabInvoice', [GateController::class, 'findAftabInvoice']);
Route::post('/findAftabInvoice/addbijac', [GateController::class, 'addbijac']);
