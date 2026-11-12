<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // En produccion todas las URLs y redirecciones deben usar HTTPS.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Mantener el contrato de la API sin el wrapper "data".
        JsonResource::withoutWrapping();
    }
}
