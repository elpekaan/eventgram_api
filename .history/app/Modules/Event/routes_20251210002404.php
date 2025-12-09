<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Event\Controllers\EventController;
use App\Modules\Event\Controllers\SearchController; // <--- Import Et

Route::prefix('events')->group(function () {

    // 1. PUBLIC ROUTES (Herkes erişebilir)
    // Arama rotası
    Route::get('/search', SearchController::class);

    // 2. PROTECTED ROUTES (Sadece giriş yapmışlar)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [EventController::class, 'store']);
    });
});
