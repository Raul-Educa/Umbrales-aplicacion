<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GraficoController extends Controller
{
    public function verGrafico($codigo)
    {
        //Muestra el grafico con el codigo de ls estación
        return view('auth.grafico', compact('codigo'));
    }
    // Coge todos los datos de la estacion y los convierte a JSON para el gáfico
    public function obtenerHistorial($codigo, Request $request)
    {
        $ultimaFechaObj = DB::table('umbrales_randatosepisodio')
            ->where('rde_estacion', $codigo)
            ->orderBy('rde_hora', 'desc')
            ->first(['rde_hora']);

        if (!$ultimaFechaObj) {
            return response()->json(['fechas' => [], 'valores' => [], 'umbrales' => []]);
        }

        $dias = $request->query('dias', 7);

        // INICIAMOS LA CONSULTA
        $query = DB::table('umbrales_randatosepisodio')
            ->where('rde_estacion', $codigo);

        if ($dias === 'all') {
            // Histórico completo (sin filtro de fecha)
        } else {
            // Restamos los días solicitados
            $fechaInicio = Carbon::parse($ultimaFechaObj->rde_hora)->subDays((int)$dias);
            $query->where('rde_hora', '>=', $fechaInicio);
        }

        // --- LÓGICA DE DENSIDAD DE DATOS (Más puntos para la gráfica) ---
        if ($dias === 'all' || $dias == 30) {
            // HISTÓRICO Y 30 DÍAS: Agrupados por HORA (24 datos diarios, adiós a las 00:00)
            $historial = $query->select(
                DB::raw("DATE_TRUNC('hour', rde_hora) as fecha_agrupada"),
                DB::raw("ROUND(AVG(rde_valor)::numeric, 2) as media_valor")
            )
                ->groupBy('fecha_agrupada')
                ->orderBy('fecha_agrupada', 'asc')
                ->get();
        } else {
            // 1, 7 y 10 DÍAS: Datos crudos (Todos los puntos exactos, cada 15m, 5m, etc.)
            $historial = $query->select(
                'rde_hora as fecha_agrupada',
                'rde_valor as media_valor'
            )
                ->orderBy('rde_hora', 'asc')
                ->get();
        }

        $fechas = [];
        $valores = [];

        foreach ($historial as $dato) {
            $fechas[] = Carbon::parse($dato->fecha_agrupada)->timestamp * 1000;
            $valores[] = (float) $dato->media_valor;
        }

        $esEmbalse = str_starts_with($codigo, 'E_');
        $prefijo = $esEmbalse ? 'er_' : 'ur_';

        $tablaUmbrales = $esEmbalse ? 'umbrales_embalsesran' : 'umbrales_umbralesran';
        $columnaCodigo = $esEmbalse ? 'er_codigo' : 'ur_codigo';

        $datosUmbral = DB::table($tablaUmbrales)
            ->where($columnaCodigo, $codigo)
            ->first();

        $umbralesExtraidos = [];

        if ($datosUmbral) {
            $u1 = $prefijo . 'umbral1';
            $u2 = $prefijo . 'umbral2';
            $u3 = $prefijo . 'umbral3';

            if (isset($datosUmbral->$u1) && $datosUmbral->$u1 > 0) {
                $umbralesExtraidos[] = ['valor' => (float)$datosUmbral->$u1, 'color' => '#facc15', 'texto' => 'Alerta 1'];
            }
            if (isset($datosUmbral->$u2) && $datosUmbral->$u2 > 0) {
                $umbralesExtraidos[] = ['valor' => (float)$datosUmbral->$u2, 'color' => '#fb923c', 'texto' => 'Alerta 2'];
            }
            if (isset($datosUmbral->$u3) && $datosUmbral->$u3 > 0) {
                $umbralesExtraidos[] = ['valor' => (float)$datosUmbral->$u3, 'color' => '#ef4444', 'texto' => 'Alerta 3'];
            }
        }

        return response()->json([
            'fechas' => $fechas,
            'valores' => $valores,
            'umbrales' => $umbralesExtraidos
        ]);
    }
}
