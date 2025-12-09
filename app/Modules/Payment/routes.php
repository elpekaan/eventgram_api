<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Payment\Controllers\MockPaymentController;

Route::prefix('payments')->group(function () {

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/mock-pay', [MockPaymentController::class, 'pay']);
    });
});
