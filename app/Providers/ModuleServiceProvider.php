<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class ModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Modules klasöründeki her klasörü tara
        $modulesPath = app_path('Modules');

        if (!File::exists($modulesPath)) {
            return;
        }

        $modules = array_map('basename', File::directories($modulesPath));

        foreach ($modules as $module) {
            $this->registerModule($module);
        }
    }

    private function registerModule(string $module): void
    {
        $modulePath = app_path("Modules/{$module}");

        // 1. Load Routes (api.php veya routes.php varsa)
        // Biz standart olarak 'routes.php' kullanacağız.
        if (File::exists($modulePath . '/routes.php')) {
            Route::middleware('api')
                ->prefix('api')
                ->group($modulePath . '/routes.php');
        }

        // 2. Load Migrations
        if (File::isDirectory($modulePath . '/Migrations')) {
            $this->loadMigrationsFrom($modulePath . '/Migrations');
        }
    }
}
