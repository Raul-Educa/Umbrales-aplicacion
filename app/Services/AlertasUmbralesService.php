<?php



namespace App\Services;

use Illuminate\Support\Facades\DB;

class AlertasUmbralesService
{

public function BuscarGlobal($query)
    {
        $query = trim($query);

        $ultimosDatos = \Illuminate\Support\Facades\DB::table('umbrales_randatosepisodio')
            ->select(\Illuminate\Support\Facades\DB::raw('DISTINCT ON (rde_estacion) rde_estacion, rde_valor, rde_hora'))
            ->orderBy('rde_estacion')
            ->orderBy('rde_hora', 'desc');

            // Hace una consulta buscando el texto que se ha introducido en esas columnas (nombre, codigo, rio y tagip21)
// HAce lo mismo para todos los casos

        $embalses = \Illuminate\Support\Facades\DB::table('umbrales_embalsesran as e')
            ->leftJoinSub($ultimosDatos, 'd', 'e.er_codigo', '=', 'd.rde_estacion')
            ->where(function($q) use ($query) {
                $q->where('e.er_nombre', 'ILIKE', "%$query%")
                  ->orWhere('e.er_codigo', 'ILIKE', "%$query%")
                  ->orWhere('e.er_rio', 'ILIKE', "%$query%")
                  ->orWhere('e.er_tag_ip21', 'ILIKE', "%$query%");
            })
            ->select(
                'e.*',
                'd.rde_valor',
                'd.rde_hora',
                \Illuminate\Support\Facades\DB::raw('CASE WHEN d.rde_valor >= e.er_umbral3 THEN 3 WHEN d.rde_valor >= e.er_umbral2 THEN 2 WHEN d.rde_valor >= e.er_umbral1 THEN 1 ELSE 0 END as nivel_alerta')
            )
            ->get();

        $roeas = \Illuminate\Support\Facades\DB::table('umbrales_umbralesran as ur')
            ->leftJoinSub($ultimosDatos, 'd', 'ur.ur_codigo', '=', 'd.rde_estacion')
            ->where('ur.ur_codigo', 'LIKE', 'R0%')
            ->where(function($q) use ($query) {
                $q->where('ur.ur_nombre', 'ILIKE', "%$query%")
                  ->orWhere('ur.ur_codigo', 'ILIKE', "%$query%")
                  ->orWhere('ur.ur_rio', 'ILIKE', "%$query%")
                  ->orWhere('ur.ur_zona_explotacion', 'ILIKE', "%$query%")
                  ->orWhere('ur.ur_tag_ip21', 'ILIKE', "%$query%");
            })
            ->select(
                'ur.*',
                'd.rde_valor',
                'd.rde_hora',
                \Illuminate\Support\Facades\DB::raw('CASE WHEN d.rde_valor >= ur.ur_umbral3 THEN 3 WHEN d.rde_valor >= ur.ur_umbral2 THEN 2 WHEN d.rde_valor >= ur.ur_umbral1 THEN 1 ELSE 0 END as nivel_alerta')
            )
            ->get();

        $marcos_control = \Illuminate\Support\Facades\DB::table('umbrales_umbralesran as ur')
            ->leftJoinSub($ultimosDatos, 'd', 'ur.ur_codigo', '=', 'd.rde_estacion')
            ->where('ur.ur_codigo', 'LIKE', 'MC%')
            ->where(function($q) use ($query) {
                $q->where('ur.ur_nombre', 'ILIKE', "%$query%")
                  ->orWhere('ur.ur_codigo', 'ILIKE', "%$query%")
                  ->orWhere('ur.ur_rio', 'ILIKE', "%$query%")
                  ->orWhere('ur.ur_zona_explotacion', 'ILIKE', "%$query%")
                  ->orWhere('ur.ur_tag_ip21', 'ILIKE', "%$query%");
            })
            ->select(
                'ur.*',
                'd.rde_valor',
                'd.rde_hora',
                \Illuminate\Support\Facades\DB::raw('CASE WHEN d.rde_valor >= ur.ur_umbral3 THEN 3 WHEN d.rde_valor >= ur.ur_umbral2 THEN 2 WHEN d.rde_valor >= ur.ur_umbral1 THEN 1 ELSE 0 END as nivel_alerta')
            )
            ->get();

        $aforos = \Illuminate\Support\Facades\DB::table('umbrales_umbralesran as ur')
            ->leftJoinSub($ultimosDatos, 'd', 'ur.ur_codigo', '=', 'd.rde_estacion')
            ->where('ur.ur_codigo', 'LIKE', 'AR%')
            ->where(function($q) use ($query) {
                $q->where('ur.ur_nombre', 'ILIKE', "%$query%")
                  ->orWhere('ur.ur_codigo', 'ILIKE', "%$query%")
                  ->orWhere('ur.ur_rio', 'ILIKE', "%$query%")
                  ->orWhere('ur.ur_zona_explotacion', 'ILIKE', "%$query%")
                  ->orWhere('ur.ur_tag_ip21', 'ILIKE', "%$query%");
            })
            ->select(
                'ur.*',
                'd.rde_valor',
                'd.rde_hora',
                \Illuminate\Support\Facades\DB::raw('CASE WHEN d.rde_valor >= ur.ur_umbral3 THEN 3 WHEN d.rde_valor >= ur.ur_umbral2 THEN 2 WHEN d.rde_valor >= ur.ur_umbral1 THEN 1 ELSE 0 END as nivel_alerta')
            )
            ->get();
// Devuelve lo encontrado en cada uno de ellos
        return [
            'embalses'       => $embalses,
            'roeas'          => $roeas,
            'marcos_control' => $marcos_control,
            'aforos'         => $aforos
        ];
    }

    /*

TODO ESTE CODIGO AHORA MISMO ESTA EN DESUSO ES LA LOGICA PARA LOS EMBALSES, AFOROS EN RIOS, MARCOS DE CONTROL Y ROEAS



    public function ResumenAlertas()
    {
        return Cache::remember('sidebar_alertas', 900, function () {
            $nuevosDatosRoeas = DB::table('umbrales_randatosepisodio')
                ->select(DB::raw('DISTINCT ON (rde_estacion) rde_estacion, rde_valor, rde_hora'))
                ->orderBy('rde_estacion')
                ->orderBy('rde_hora', 'desc');

            $datos = DB::table('umbrales_embalsesran as e')
                ->joinSub($nuevosDatosRoeas, 'd', function ($join) {
                    $join->on('e.er_codigo', '=', 'd.rde_estacion');
                })
                ->select(
                    'e.er_comunidad_autonoma_id',
                    DB::raw('CASE
                WHEN d.rde_valor >= e.er_umbral3 THEN 3
                WHEN d.rde_valor >= e.er_umbral2 THEN 2
                WHEN d.rde_valor >= e.er_umbral1 THEN 1
                ELSE 0 END as nivel'),
                    DB::raw('COUNT(*) as total')
                )
                ->whereRaw('d.rde_valor >= e.er_umbral1')
                ->groupBy('e.er_comunidad_autonoma_id', 'nivel')
                ->get();
            Cache::put('ultima_hora_conexion', date('H:i'), 1440);
            return $datos->groupBy('er_comunidad_autonoma_id');
        });
    }

    public function DetalleAlertasPorCCAA($ccaaId)
    {
        return Cache::remember('embalsesCCAA' . $ccaaId, 900, function () use ($ccaaId) {
            $nuevosDatosRoeas = DB::table('umbrales_randatosepisodio')
                ->select(DB::raw('DISTINCT ON (rde_estacion) rde_estacion, rde_valor, rde_hora'))
                ->orderBy('rde_estacion')
                ->orderBy('rde_hora', 'desc');

            return DB::table('umbrales_embalsesran as e')
                ->leftJoinSub($nuevosDatosRoeas, 'd', function ($join) {
                    $join->on('e.er_codigo', '=', 'd.rde_estacion');
                })
                ->where('e.er_comunidad_autonoma_id', $ccaaId)
                ->select(
                    'e.*',
                    'd.rde_valor',
                    'd.rde_hora',
                    DB::raw('CASE
                WHEN d.rde_valor >= e.er_umbral3 THEN 3
                WHEN d.rde_valor >= e.er_umbral2 THEN 2
                WHEN d.rde_valor >= e.er_umbral1 THEN 1
                ELSE 0 END as nivel_alerta')
                )
                ->get();
        });
    }
        */


/*
    public function TotalAlertasGlobales()
    {
        $resumenEmbalses = $this->ResumenAlertas();
        $totalEmbalses = 0;
        foreach ($resumenEmbalses as $comunidad) {
            $totalEmbalses += $comunidad->sum('total');
        }

        $todasLasRoeas = $this->EstadoRoeas();
        $totalRoeas = $todasLasRoeas->where('nivel_alerta', '>', 0)->count();
        $roeasA3 = $todasLasRoeas->where('nivel_alerta', 3)->count();
        $roeasA2 = $todasLasRoeas->where('nivel_alerta', 2)->count();
        $roeasA1 = $todasLasRoeas->where('nivel_alerta', 1)->count();

        $todosLosMC = $this->EstadoMarcosControl();
        $totalMC = $todosLosMC->where('nivel_alerta', '>', 0)->count();
        $mcA3 = $todosLosMC->where('nivel_alerta', 3)->count();
        $mcA2 = $todosLosMC->where('nivel_alerta', 2)->count();
        $mcA1 = $todosLosMC->where('nivel_alerta', 1)->count();

        $todosAR = $this->EstadoAforosRios();
        $totalAR = count($todosAR);

        return [
            'total' => $totalEmbalses,
            'total_roeas' => $totalRoeas,
            'roeas_alerta_3' => $roeasA3,
            'roeas_alerta_2' => $roeasA2,
            'roeas_alerta_1' => $roeasA1,
            'total_mc' => $totalMC,
            'mc_alerta_3' => $mcA3,
            'mc_alerta_2' => $mcA2,
            'mc_alerta_1' => $mcA1,
            'total_ar' => $totalAR,
        ];
    }

    public function BuscarEmbalses($termino)
    {
        $nuevosDatosRoeas = DB::table('umbrales_randatosepisodio')
            ->select(DB::raw('DISTINCT ON (rde_estacion) rde_estacion, rde_valor, rde_hora'))
            ->orderBy('rde_estacion')
            ->orderBy('rde_hora', 'desc');

        $subQuery = DB::table('umbrales_embalsesran as e')
            ->leftJoinSub($nuevosDatosRoeas, 'd', function ($join) {
                $join->on('e.er_codigo', '=', 'd.rde_estacion');
            })
            ->select(
                'e.*',
                'd.rde_valor',
                'd.rde_hora',
                DB::raw('CASE
                WHEN d.rde_valor >= e.er_umbral3 THEN 3
                WHEN d.rde_valor >= e.er_umbral2 THEN 2
                WHEN d.rde_valor >= e.er_umbral1 THEN 1
                ELSE 0 END as nivel_alerta')
            );

        $consulta = DB::table(DB::raw("({$subQuery->toSql()}) as resultados"))
            ->mergeBindings($subQuery);


        if (in_array($termino, ['1', '2', '3'])) {
            return $consulta->where('nivel_alerta', '=', (int)$termino)->get();
        }

        return $consulta->where(function ($q) use ($termino) {
            $q->where('er_nombre', 'ILIKE', "%{$termino}%")
                ->orWhere('er_rio', 'ILIKE', "%{$termino}%")
                ->orWhere('er_municipio', 'ILIKE', "%{$termino}%")
                ->orWhere('er_provincia', 'ILIKE', "%{$termino}%")
                ->orWhere('er_codigo', 'ILIKE', "%{$termino}%")
                ->orWhere(DB::raw('CAST(rde_valor AS TEXT)'), 'LIKE', "%{$termino}%");
        })->get();
    }

    public function EstadoRoeas()
    {
        return \Illuminate\Support\Facades\Cache::remember('roeasDetalle', 900, function () {

            $nuevosDatos = \Illuminate\Support\Facades\DB::table('umbrales_randatosepisodio')
                ->select(\Illuminate\Support\Facades\DB::raw('DISTINCT ON (rde_estacion) rde_estacion, rde_valor, rde_hora'))
                ->where('rde_estacion', 'LIKE', 'R0%')
                ->orderBy('rde_estacion')
                ->orderBy('rde_hora', 'desc');

            return \Illuminate\Support\Facades\DB::table('umbrales_umbralesran as ur')
                ->leftJoinSub($nuevosDatos, 'ultimas', 'ur.ur_codigo', '=', 'ultimas.rde_estacion')
                ->where('ur.ur_codigo', 'LIKE', 'R0%')
                ->select(
                    'ur.ur_codigo',
                    'ur.ur_nombre',
                    'ur.ur_rio',
                    'ur.ur_activo',
                    'ur.ur_zona_explotacion',
                    'ur.ur_ccaa_influencia',
                    'ur.ur_ultimo_nivel_alerta',
                    'ur.ur_tag_ip21',
                    'ur.ur_tag_ip21_caudal',
                    'ur.ur_tag_digital_ip21',
                    'ultimas.rde_valor',
                    'ultimas.rde_hora',
                    \Illuminate\Support\Facades\DB::raw('CASE
                        WHEN ultimas.rde_valor >= ur.ur_umbral3 THEN 3
                        WHEN ultimas.rde_valor >= ur.ur_umbral2 THEN 2
                        WHEN ultimas.rde_valor >= ur.ur_umbral1 THEN 1
                        ELSE 0 END as nivel_alerta')
                )
                ->orderBy('nivel_alerta', 'desc')
                ->get();
        });
    }

    public function getNombresCCAA()
    {
        return DB::table('umbrales_ccaa')
            ->pluck('c_comunidad_autonoma', 'c_id')
            ->toArray();
    }

    public function EstadoMarcosControl()
    {
        return \Illuminate\Support\Facades\Cache::remember('mcDetalle', 900, function () {

            $nuevosDatos = \Illuminate\Support\Facades\DB::table('umbrales_randatosepisodio')
                ->select(\Illuminate\Support\Facades\DB::raw('DISTINCT ON (rde_estacion) rde_estacion, rde_valor, rde_hora'))
                ->where('rde_estacion', 'LIKE', 'MC%')
                ->orderBy('rde_estacion')
                ->orderBy('rde_hora', 'desc');

            return \Illuminate\Support\Facades\DB::table('umbrales_umbralesran as ur')
                ->leftJoinSub($nuevosDatos, 'ultimas', 'ur.ur_codigo', '=', 'ultimas.rde_estacion')
                ->where('ur.ur_codigo', 'LIKE', 'MC%')
                ->select(
                    'ur.ur_codigo',
                    'ur.ur_nombre',
                    'ur.ur_rio',
                    'ur.ur_activo',
                    'ur.ur_zona_explotacion',
                    'ur.ur_ccaa_influencia',
                    'ur.ur_ultimo_nivel_alerta',
                    'ur.ur_tag_ip21',
                    'ur.ur_tag_ip21_caudal',
                    'ur.ur_tag_digital_ip21',
                    'ultimas.rde_valor',
                    'ultimas.rde_hora',
                    \Illuminate\Support\Facades\DB::raw('CASE
                        WHEN ultimas.rde_valor >= ur.ur_umbral3 THEN 3
                        WHEN ultimas.rde_valor >= ur.ur_umbral2 THEN 2
                        WHEN ultimas.rde_valor >= ur.ur_umbral1 THEN 1
                        ELSE 0 END as nivel_alerta')
                )
                ->orderBy('nivel_alerta', 'desc')
                ->get();
        });
    }

    public function EstadoAforosRios()
    {
        return \Illuminate\Support\Facades\Cache::remember('ar_detalle_v1', 900, function () {

            $nuevosDatos = \Illuminate\Support\Facades\DB::table('umbrales_randatosepisodio')
                ->select(\Illuminate\Support\Facades\DB::raw('DISTINCT ON (rde_estacion) rde_estacion, rde_valor, rde_hora'))
                ->where('rde_estacion', 'LIKE', 'AR%')
                ->orderBy('rde_estacion')
                ->orderBy('rde_hora', 'desc');

            return \Illuminate\Support\Facades\DB::table('umbrales_umbralesran as ur')
                ->leftJoinSub($nuevosDatos, 'ultimas', 'ur.ur_codigo', '=', 'ultimas.rde_estacion')
                ->where('ur.ur_codigo', 'LIKE', 'AR%')
                ->select(
                    'ur.*',
                    'ultimas.rde_valor',
                    'ultimas.rde_hora',
                    \Illuminate\Support\Facades\DB::raw('CASE
                        WHEN ultimas.rde_valor >= ur.ur_umbral3 THEN 3
                        WHEN ultimas.rde_valor >= ur.ur_umbral2 THEN 2
                        WHEN ultimas.rde_valor >= ur.ur_umbral1 THEN 1
                        ELSE 0 END as nivel_alerta')
                )
                ->orderBy('nivel_alerta', 'desc')
                ->get();
        });
    }

    public function ResumenAforosRios()
    {
        $todosAR = $this->EstadoAforosRios();
        $resumen = [];

        foreach ($todosAR as $ar) {
            if ($ar->ur_ccaa_influencia) {
                $ids = explode(',', $ar->ur_ccaa_influencia);
                foreach($ids as $id) {
                    $idLimpio = trim($id);
                    if (!isset($resumen[$idLimpio])) {
                        $resumen[$idLimpio] = collect();
                    }
                    $resumen[$idLimpio]->push($ar);
                }
            }
        }

        $resultadoFinal = [];
        foreach ($resumen as $ccaa_id => $estaciones) {
            $resultadoFinal[$ccaa_id] = $estaciones->groupBy('nivel_alerta')->map(function($grupo, $nivel) {
                return (object)[
                    'nivel' => $nivel,
                    'total' => $grupo->count()
                ];
            })->values();
        }

        return collect($resultadoFinal);
    }
*/
}
