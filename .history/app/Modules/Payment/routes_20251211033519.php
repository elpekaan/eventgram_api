<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Payment\Controllers\MockPaymentController;
use App\Modules\Payment\Controllers\IyzicoWebhookController;
use App\Modules\Payment\Controllers\MockChargebackController;


Route::prefix('payments')->group(function () {

    // Webhook (Auth gerektirmez, İyzico çağırır)
    Route::post('/webhook/iyzico', [IyzicoWebhookController::class, 'handlePayment']);

    // Mock Pay (Auth gerekir)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/mock-pay', [MockPaymentController::class, 'pay']);
    });
    // Mock Chargeback (Admin Only - Simülasyon)
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::post('/mock-chargeback', [MockChargebackController::class, 'trigger']);
    });
});
