<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Transfer\Controllers\TicketTransferController;

Route::prefix('transfers')->group(function () {

    // Sadece giriş yapmış kullanıcılar transfer başlatabilir
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [TicketTransferController::class, 'store']);
        Route::post('/{id}/accept', [TicketTransferController::class, 'accept']);
    });
});
