<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Event\Controllers\EventController;

Route::prefix('events')->group(function () {

    // Auth zorunlu
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [EventController::class, 'store']);
    });
});
