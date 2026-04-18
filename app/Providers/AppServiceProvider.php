<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Models\Vehiculo;
use App\Models\Diag;
use App\Observers\VehiculoObserver;
use App\Observers\DiagObserver;

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
        Vehiculo::observe(VehiculoObserver::class);
        Diag::observe(DiagObserver::class);
    }
}
