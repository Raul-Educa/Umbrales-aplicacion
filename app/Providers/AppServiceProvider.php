<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AlertasUmbralesService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;

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
    public function boot(AlertasUmbralesService $alertasService)
    {
        View::composer('auth.plantilla', function ($view) use ($alertasService) {

        // Guarda el resultado en la cache durante 5 min para tardar menos
        $resumen = Cache::remember('sidebar_alertas', 300, function () use ($alertasService) {
            return $alertasService->ResumenAlertas();
        });

        $totalGlobal = 0;
        foreach($resumen as $comunidad) {
            $totalGlobal += $comunidad->sum('total');
        }

        $view->with('resumenAlertas', $resumen);
        $view->with('totalGlobal', $totalGlobal);
    });
    }
}
