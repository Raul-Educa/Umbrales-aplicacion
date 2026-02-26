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


            $resumen = Cache::remember('sidebar_alertas', 900, function () use ($alertasService) {
                // Esto solo se ejecutará 1 vez cada 15 minutos
                return $alertasService->ResumenAlertas();
            });

            // 2. Calculamos el total global usando los datos cacheados (Rapidísimo)
            $totalGlobal = 0;
            foreach ($resumen as $comunidad) {
                $totalGlobal += $comunidad->sum('total');
            }

            // 3. Pasamos las variables a la vista
            $view->with('resumenAlertas', $resumen);
            $view->with('totalGlobal', $totalGlobal);
        });
    }
}
