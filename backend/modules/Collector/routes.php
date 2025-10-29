<?php
use Modules\Collector\Controllers\CustomerController;

Route::get('/customers/all', [CustomerController::class, 'all']);
