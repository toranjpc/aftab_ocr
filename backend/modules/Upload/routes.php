<?php
use Illuminate\Support\Facades\Route;
use Modules\Upload\Controllers\UploadFileController;

Route::post('upload-file', [UploadFileController::class, 'store'])
->withoutMiddleware(['api']);
