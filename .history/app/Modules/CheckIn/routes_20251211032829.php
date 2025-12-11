<?php

use Illuminate\Support\Facades\Route;
use App\Modules\CheckIn\Controllers\CheckInController;

Route::prefix('check-in')->group(function () {

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [CheckInController::class, 'checkIn']);
    });
});
