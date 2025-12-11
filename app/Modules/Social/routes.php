<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Social\Controllers\FollowController;
use App\Modules\Social\Controllers\FeedController;

Route::prefix('social')->middleware('auth:sanctum')->group(function () {

    // Follow/Unfollow
    Route::post('/follow/venue/{id}', [FollowController::class, 'toggleVenue']);

    // User Feed
    Route::get('/feed', [FeedController::class, 'index']);
});
