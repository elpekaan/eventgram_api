<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Event\Controllers\EventController;
use App\Modules\Event\Controllers\SearchController;
use App\Modules\Order\Controllers\OrderController;
use App\Modules\CheckIn\Controllers\CheckInController;
use App\Modules\Transfer\Controllers\TicketTransferController;
use App\Modules\Venue\Controllers\VenueController;
use App\Modules\Venue\Controllers\AdminVenueController;
use App\Modules\Venue\Controllers\VenueTransferController;
use App\Modules\Social\Controllers\FeedController;
use App\Modules\Social\Controllers\FollowController;
use App\Modules\Payment\Controllers\IyzicoWebhookController;
use App\Modules\Payment\Controllers\MockPaymentController;
use App\Modules\Payment\Controllers\MockChargebackController;

/*
|--------------------------------------------------------------------------
| API Routes - Eventgram API v1
|--------------------------------------------------------------------------
*/

// ============================================================================
// PUBLIC ROUTES (No Authentication Required)
// ============================================================================

Route::prefix('v1')->group(function () {
    
    // AUTH
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    // SEARCH (Public)
    Route::get('/search', SearchController::class);

    // WEBHOOKS (No Auth)
    Route::prefix('webhooks')->group(function () {
        Route::post('/iyzico/payment', [IyzicoWebhookController::class, 'handlePayment']);
    });

    // MOCK PAYMENT (Development Only)
    Route::prefix('mock')->group(function () {
        Route::post('/payment', [MockPaymentController::class, 'pay']);
        Route::post('/chargeback', [MockChargebackController::class, 'trigger']);
    });
});

// ============================================================================
// AUTHENTICATED ROUTES
// ============================================================================

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    
    // AUTH
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    // EVENTS
    Route::prefix('events')->group(function () {
        Route::post('/', [EventController::class, 'store']);
        // Route::get('/', [EventController::class, 'index']);
        // Route::get('/{id}', [EventController::class, 'show']);
        // Route::put('/{id}', [EventController::class, 'update']);
        // Route::delete('/{id}', [EventController::class, 'destroy']);
    });

    // ORDERS
    Route::prefix('orders')->group(function () {
        Route::post('/', [OrderController::class, 'store']);
        // Route::get('/', [OrderController::class, 'index']);
        // Route::get('/{id}', [OrderController::class, 'show']);
    });

    // CHECK-IN
    Route::prefix('check-in')->group(function () {
        Route::post('/', [CheckInController::class, 'checkIn']);
    });

    // TICKET TRANSFERS
    Route::prefix('transfers')->group(function () {
        Route::post('/', [TicketTransferController::class, 'store']);
        Route::post('/{id}/accept', [TicketTransferController::class, 'accept']);
        
        // Venue owner approval
        Route::post('/{id}/venue-approve', [VenueTransferController::class, 'approve']);
    });

    // VENUES
    Route::prefix('venues')->group(function () {
        Route::post('/', [VenueController::class, 'store']);
        
        // Admin routes
        Route::prefix('admin')->group(function () {
            Route::post('/{id}/approve', [AdminVenueController::class, 'approve']);
            Route::post('/{id}/reject', [AdminVenueController::class, 'reject']);
        });
    });

    // SOCIAL
    Route::prefix('social')->group(function () {
        Route::get('/feed', [FeedController::class, 'index']);
        Route::post('/venues/{id}/follow', [FollowController::class, 'toggleVenue']);
    });
});

