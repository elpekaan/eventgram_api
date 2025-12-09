<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Venue\Controllers\VenueController;

Route::prefix('venues')->group(function () {

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [VenueController::class, 'store']);
    });
});
