<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Auth Service Binding
        $this->app->bind(
            \App\Contracts\Services\AuthServiceInterface::class,
            \App\Modules\Auth\Services\AuthService::class
        );

        // CheckIn Service Binding
        $this->app->bind(
            \App\Contracts\Services\CheckInServiceInterface::class,
            \App\Modules\CheckIn\Services\CheckInService::class
        );

        // Event Service Binding
        $this->app->bind(
            \App\Contracts\Services\EventServiceInterface::class,
            \App\Modules\Event\Services\EventService::class
        );

        // Order Service Binding
        $this->app->bind(
            \App\Contracts\Services\OrderServiceInterface::class,
            \App\Modules\Order\Services\OrderService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Scramble Authentication Ayarı (Sanctum için)
        Scramble::afterOpenApiGenerated(function (OpenApi $openApi) {
            $openApi->secure(
                SecurityScheme::http('bearer')
            );
        });

        // Dokümana erişim güvenliği (Local'de herkese açık)
        Scramble::configure()
            ->routes(function ($router) {
                return $router->prefix('docs/api')
                    ->middleware('web');
            });
    }
}
