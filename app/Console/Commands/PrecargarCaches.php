<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AlertasUmbralesService;

class PrecargarCaches extends Command
{
    protected $signature = 'cache:precargar';

    protected $description = 'Precarga la cache para no tener problemas de lentitud en las visitas';

    public function handle(AlertasUmbralesService $alertasService)
    {

        $alertasService->ResumenAlertas();

        $comunidades = [
            8,  // Castilla La Mancha
            11, // Extremadura
            13, // Madrid
            7   // Castilla y León
        ];

        // 3. El robot "visita" cada comunidad por nosotros
        foreach ($comunidades as $id) {
            $alertasService->DetalleAlertasPorCCAA($id);
        }
    }
}
