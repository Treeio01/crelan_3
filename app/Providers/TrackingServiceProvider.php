<?php

namespace App\Providers;

use App\Services\DeviceDetectionService;
use App\Services\LocationService;
use App\Services\PreSessionService;
use Illuminate\Support\ServiceProvider;

class TrackingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(LocationService::class);
        $this->app->singleton(DeviceDetectionService::class);
        $this->app->singleton(PreSessionService::class, function ($app) {
            return new PreSessionService(
                $app->make(LocationService::class),
                $app->make(DeviceDetectionService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
