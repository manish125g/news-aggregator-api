<?php

namespace App\Providers;

use App\Services\NewsAggregatorService;
use App\Services\NewsProviders\GuardianProvider;
use App\Services\NewsProviders\NewsAPIProvider;
use App\Services\NewsProviders\NYTProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(NewsAggregatorService::class, function ($app) {
            return new NewsAggregatorService([
                new NewsAPIProvider(),
                new GuardianProvider(),
                new NYTProvider()
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
