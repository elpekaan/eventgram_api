<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Venue\Controllers\VenueController;
use App\Modules\Venue\Controllers\AdminVenueController; //

Route::prefix('venues')->group(function () {

    // 1. MEKAN SAHİBİ Route'ları (Auth zorunlu)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [VenueController::class, 'store']);
    });

    // 2. ADMIN Route'ları (Auth + Admin zorunlu)
    Route::middleware(['auth:sanctum', 'admin'])
        ->prefix('admin')
        ->group(function () {
            // PATCH: Mekan statüsünü değiştir (Approve/Reject)
            Route::patch('/{id}/status', [AdminVenueController::class, 'updateStatus']);
        });
});
