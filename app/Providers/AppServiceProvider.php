<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Models\Vehiculo;
use App\Models\Diag;
use App\Observers\VehiculoObserver;
use App\Observers\DiagObserver;
// Importamos la fachada URL
use Illuminate\Support\Facades\URL;

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
        // Forzar la URL base y esquema HTTPS en entornos de producción (cPanel)
        if (config('app.env') === 'production') {
            URL::forceRootUrl(config('app.url'));
            
            if (str_contains(config('app.url'), 'https://')) {
                URL::forceScheme('https');
            }

            // Truco maestro: Corregir el SCRIPT_NAME para que Laravel entienda el subdirectorio
            // sin necesidad de usar Route::prefix('modprev')
            $path = parse_url(config('app.url'), PHP_URL_PATH);
            if ($path) {
                $this->app['request']->server->set('SCRIPT_NAME', rtrim($path, '/') . '/index.php');
            }
        }

        Vehiculo::observe(VehiculoObserver::class);
        Diag::observe(DiagObserver::class);
    }
}
