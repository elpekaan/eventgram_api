<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(\App\Contracts\Services\AuthServiceInterface::class, \App\Modules\Auth\Services\AuthService::class);
        $this->app->bind(\App\Contracts\Services\CheckInServiceInterface::class, \App\Modules\CheckIn\Services\CheckInService::class);
        $this->app->bind(\App\Contracts\Services\EventServiceInterface::class, \App\Modules\Event\Services\EventService::class);
        $this->app->bind(\App\Contracts\Services\OrderServiceInterface::class, \App\Modules\Order\Services\OrderService::class);
        $this->app->bind(\App\Contracts\Services\TicketServiceInterface::class, \App\Modules\Ticket\Services\TicketService::class);
        $this->app->bind(\App\Contracts\Services\TicketTransferServiceInterface::class, \App\Modules\Transfer\Services\TicketTransferService::class);
        $this->app->bind(\App\Contracts\Services\RefundServiceInterface::class, \App\Modules\Payment\Services\RefundService::class);
        $this->app->bind(\App\Contracts\Services\ChargebackServiceInterface::class, \App\Modules\Payment\Services\ChargebackService::class);
        $this->app->bind(\App\Contracts\Services\FeedServiceInterface::class, \App\Modules\Social\Services\FeedService::class);
        $this->app->bind(\App\Contracts\Services\FollowServiceInterface::class, \App\Modules\Social\Services\FollowService::class);
        $this->app->bind(\App\Contracts\Services\VenueServiceInterface::class, \App\Modules\Venue\Services\VenueService::class);
        $this->app->bind(\App\Contracts\Services\PaymentReconciliationServiceInterface::class, \App\Modules\Payment\Services\PaymentReconciliationService::class);
    }

    public function boot(): void
    {
        Scramble::afterOpenApiGenerated(function (OpenApi $openApi) {
            $openApi->secure(SecurityScheme::http('bearer'));
        });
        Scramble::configure()->routes(function ($router) {
            return $router->prefix('docs/api')->middleware('web');
        });
    }
}
