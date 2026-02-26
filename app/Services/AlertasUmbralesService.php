<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AlertasUmbralesService
{
    public function ResumenAlertas()
{
    $ultimasLecturas = DB::table('umbrales_randatosepisodio')
        ->select(DB::raw('DISTINCT ON (rde_estacion) rde_estacion, rde_valor, rde_hora'))
        ->orderBy('rde_estacion')
        ->orderBy('rde_hora', 'desc');

    $datos = DB::table('umbrales_embalsesran as e')
        ->joinSub($ultimasLecturas, 'd', function ($join) {
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

    return $datos->groupBy('er_comunidad_autonoma_id');
}

   public function DetalleAlertasPorCCAA($ccaaId)
{
    $ultimasLecturas = DB::table('umbrales_randatosepisodio')
        ->select(DB::raw('DISTINCT ON (rde_estacion) rde_estacion, rde_valor, rde_hora'))
        ->orderBy('rde_estacion')
        ->orderBy('rde_hora', 'desc');

    return DB::table('umbrales_embalsesran as e')
        ->leftJoinSub($ultimasLecturas, 'd', function ($join) {
            $join->on('e.er_codigo', '=', 'd.rde_estacion');
        })
        ->where('e.er_comunidad_autonoma_id', $ccaaId)
        ->select('e.*', 'd.rde_valor', 'd.rde_hora',
            DB::raw('CASE
                WHEN d.rde_valor >= e.er_umbral3 THEN 3
                WHEN d.rde_valor >= e.er_umbral2 THEN 2
                WHEN d.rde_valor >= e.er_umbral1 THEN 1
                ELSE 0 END as nivel_alerta')
        )
        ->get();
}
public function BusquedaGlobal($termino)
{
    $ultimasLecturas = DB::table('umbrales_randatosepisodio')
        ->select(DB::raw('DISTINCT ON (rde_estacion) rde_estacion, rde_valor, rde_hora'))
        ->orderBy('rde_estacion')
        ->orderBy('rde_hora', 'desc');

    $query = DB::table('umbrales_embalsesran as e')
        ->leftJoinSub($ultimasLecturas, 'd', function ($join) {
            $join->on('e.er_codigo', '=', 'd.rde_estacion');
        })
        ->select('e.*', 'd.rde_valor', 'd.rde_hora',
            DB::raw('CASE
                WHEN d.rde_valor >= e.er_umbral3 THEN 3
                WHEN d.rde_valor >= e.er_umbral2 THEN 2
                WHEN d.rde_valor >= e.er_umbral1 THEN 1
                ELSE 0 END as nivel_alerta')
        );

    return $query->where(function($q) use ($termino) {
        $q->where('e.er_nombre', 'ILIKE', "%{$termino}%")
          ->orWhere('e.er_rio', 'ILIKE', "%{$termino}%")
          ->orWhere('e.er_municipio', 'ILIKE', "%{$termino}%")
          ->orWhere('e.er_provincia', 'ILIKE', "%{$termino}%")
          ->orWhere('e.er_codigo', 'ILIKE', "%{$termino}%");
    })->get();
}

public function TotalAlertasGlobales()
{
    $resumen = $this->ResumenAlertas();
    $total = 0;
    foreach($resumen as $comunidad) {
        $total += $comunidad->sum('total');
    }
    return $total;
}
public function BuscarEmbalses($termino)
{
    $ultimasLecturas = DB::table('umbrales_randatosepisodio')
        ->select(DB::raw('DISTINCT ON (rde_estacion) rde_estacion, rde_valor, rde_hora'))
        ->orderBy('rde_estacion')
        ->orderBy('rde_hora', 'desc');

    $subQuery = DB::table('umbrales_embalsesran as e')
        ->leftJoinSub($ultimasLecturas, 'd', function ($join) {
            $join->on('e.er_codigo', '=', 'd.rde_estacion');
        })
        ->select('e.*', 'd.rde_valor', 'd.rde_hora',
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

    return $consulta->where(function($q) use ($termino) {
        $q->where('er_nombre', 'ILIKE', "%{$termino}%")
          ->orWhere('er_rio', 'ILIKE', "%{$termino}%")
          ->orWhere('er_municipio', 'ILIKE', "%{$termino}%")
          ->orWhere('er_provincia', 'ILIKE', "%{$termino}%")
          ->orWhere('er_codigo', 'ILIKE', "%{$termino}%")
          ->orWhere(DB::raw('CAST(rde_valor AS TEXT)'), 'LIKE', "%{$termino}%");
    })->get();
}
}
