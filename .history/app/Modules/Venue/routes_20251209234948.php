<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Venue\Controllers\VenueController;

Route::prefix('venues')->group(function () {

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [VenueController::class, 'store']);
    });
});

Route::prefix('venues')->group(function () {

    // ... user route'ları ...

    // ADMIN Routes
    // Hem giriş yapmış olmalı (auth:sanctum) hem de admin olmalı (admin)
    Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
        // PATCH: Kısmi güncelleme için kullanılır
        Route::patch('/{id}/status', [AdminVenueController::class, 'updateStatus']);
    });
});
