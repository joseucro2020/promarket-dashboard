<?php

namespace App\Services\WasenderApi;

use Illuminate\Support\ServiceProvider;
use App\Services\WasenderApi\WasenderClient;

class WasenderServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(base_path('config/wasenderapi.php'), 'wasenderapi');

        $this->app->singleton('wasender.client', function ($app) {
            $apiKey = config('wasenderapi.api_key') ?: env('WASENDERAPI_API_KEY');
            return new WasenderClient($apiKey);
        });
    }

    public function boot()
    {
        // Publish config
        $this->publishes([
            base_path('config/wasenderapi.php') => config_path('wasenderapi.php'),
        ], 'wasenderapi-config');

        // Load routes if present
        if (file_exists(base_path('routes/wasender.php'))) {
            $this->loadRoutesFrom(base_path('routes/wasender.php'));
        }
    }
}
