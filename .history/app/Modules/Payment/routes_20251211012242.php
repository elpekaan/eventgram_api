<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Payment\Controllers\MockPaymentController;
use App\Modules\Payment\Controllers\IyzicoWebhookController; // <--- Import

Route::prefix('payments')->group(function () {

    // Webhook (Auth gerektirmez, İyzico çağırır)
    Route::post('/webhook/iyzico', [IyzicoWebhookController::class, 'handlePayment']);

    // Mock Pay (Auth gerekir)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/mock-pay', [MockPaymentController::class, 'pay']);
    });
});
