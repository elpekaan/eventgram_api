<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Order\Controllers\OrderController;

Route::prefix('orders')->group(function () {

    // Sipariş vermek için giriş şart
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [OrderController::class, 'store']);
    });
});
