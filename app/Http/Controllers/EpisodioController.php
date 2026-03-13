<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EpisodioController extends Controller
{
    public function inicio()
    {
        // Guarda solo los episodios activos
        $episodiosActivos = DB::table('umbrales_ranepisodio')->whereNull('re_hora_fin')->get();
        // Colección para guardar todas las alertas
        $todasLasAlertas = collect();

        foreach ($episodiosActivos as $episodio) {
            // Si hay varias estaciones qutamos las comas y espacios y lo guardamos en un array
            $codigosEstaciones = !empty($episodio->re_estaciones_historicas) ? explode(',', $episodio->re_estaciones_historicas) : [];
            $codigos = array_filter(array_map('trim', $codigosEstaciones));

            if (count($codigos) > 0) {
                $mediciones = DB::table(function ($query) use ($episodio, $codigos) {
                    $query->select(
                        'rde_estacion',
                        'rde_valor',
                        'rde_valor_accesorio',
                        'rde_hora',
                        // Agrupa por código de estación (PARTITION BY)
                        // Asigna un numero de la fila para saber cual es el anterior y el nuevo (ROW_NUMBER())
                        DB::raw('ROW_NUMBER() OVER (PARTITION BY rde_estacion ORDER BY rde_hora DESC) as posicion')
                    )
                        ->from('umbrales_randatosepisodio')
                        ->where('rde_ran_episodio_id', $episodio->re_id)
                        ->whereIn('rde_estacion', $codigos);
                }, 'subconsulta')
                    ->where('posicion', '<=', 2)
                    ->get()
                    ->groupBy('rde_estacion');

                //AFOROS Y RIOS POR CCAA
                $ran = DB::table('umbrales_umbralesran')
                    ->leftJoin('umbrales_ccaa', 'umbrales_umbralesran.ur_comunidad_autonoma_id', '=', 'umbrales_ccaa.c_id')
                    ->whereIn('ur_codigo', $codigos)
                    ->get()
                    ->map(function ($item) use ($mediciones, $episodio) {
                        $m = collect($mediciones->get($item->ur_codigo, []));
                        $actual = $m->where('posicion', 1)->first();
                        $anterior = $m->where('posicion', 2)->first();
                        return (object)[
                            'tipo' => 'aforo',
                            'codigo' => $item->ur_codigo,
                            'nombre' => $item->ur_nombre,
                            'rio' => $item->ur_rio,
                            'ccaa' => $item->c_comunidad_autonoma ?? '---',
                            'tag_salida' => $item->ur_tag_ip21,
                            'tag_secundario' => $item->ur_tag_ip21_caudal ?? '---',
                            'valor' => $actual ? $actual->rde_valor : null,
                            'valor_acc' => $actual ? $actual->rde_valor_accesorio : null,
                            'hora' => $actual ? Carbon::parse($actual->rde_hora)->format('d/m/Y H:i:s') : '---',
                            'nivel_alerta' => $this->calcularNivelDinamico($actual ? $actual->rde_valor : null, $item, 'ur_'),
                            'ultimo_nivel_alerta' => $this->calcularNivelDinamico($anterior ? $anterior->rde_valor : null, $item, 'ur_'),
                            'episodio_id' => $episodio->re_id,
                            'umbral1' => $item->ur_umbral1 ?? 0,
                            'umbral2' => $item->ur_umbral2 ?? 0,
                            'umbral3' => $item->ur_umbral3 ?? 0,
                        ];
                    });

                // EMBALSES POR CCAA
                $embalses = DB::table('umbrales_embalsesran')
                    ->leftJoin('umbrales_ccaa', 'umbrales_embalsesran.er_comunidad_autonoma_id', '=', 'umbrales_ccaa.c_id')
                    ->whereIn('er_codigo', $codigos)
                    ->get()
                    ->map(function ($item) use ($mediciones, $episodio) {
                        $m = collect($mediciones->get($item->er_codigo, []));
                        $actual = $m->where('posicion', 1)->first();
                        $anterior = $m->where('posicion', 2)->first();
                        return (object)[
                            'tipo' => 'embalse',
                            'codigo' => $item->er_codigo,
                            'nombre' => $item->er_nombre,
                            'rio' => $item->er_rio ?? '---',
                            'ccaa' => $item->c_comunidad_autonoma ?? '---',
                            'tag_salida' => $item->er_tag_ip21 ?? '---',
                            'tag_secundario' => $item->er_tag_volumen ?? '---',
                            'valor' => $actual ? $actual->rde_valor : null,
                            'valor_acc' => $actual ? $actual->rde_valor_accesorio : null,
                            'hora' => $actual ? Carbon::parse($actual->rde_hora)->format('d/m/Y H:i:s') : '---',
                            'nivel_alerta' => $this->calcularNivelDinamico($actual ? $actual->rde_valor : null, $item, 'er_'),
                            'ultimo_nivel_alerta' => $this->calcularNivelDinamico($anterior ? $anterior->rde_valor : null, $item, 'er_'),
                            'episodio_id' => $episodio->re_id,
                            'umbral1' => $item->er_umbral1 ?? 0,
                            'umbral2' => $item->er_umbral2 ?? 0,
                            'umbral3' => $item->er_umbral3 ?? 0,
                        ];
                    });

                // Une alertas de afotos y de embalses
                $todasLasAlertas = $todasLasAlertas->merge($ran)->merge($embalses);
            }
        }
        // Coge solo las alertas activas y ordena de mayor a menor
        $alertas = $todasLasAlertas->whereIn('nivel_alerta', [1, 2, 3])->sortByDesc('nivel_alerta');

        return view('auth.inicio_umbrales', [
            'titulo' => 'Panel Principal - Alertas Activas',
            'aforos' => $alertas->where('tipo', 'aforo')->values(),
            'embalses' => $alertas->where('tipo', 'embalse')->values(),
        ]);
    }

    // --- NUEVA FUNCIÓN: Extrae y calcula los niveles de alerta para las tablas ---
    private function obtenerNivelesAlertaVarios($episodios)
    {
        $codigos = [];
        foreach ($episodios as $ep) {
            if (!empty($ep->re_estaciones_historicas)) {
                $codigos = array_merge($codigos, explode(',', $ep->re_estaciones_historicas));
            }
            if (!empty($ep->re_estaciones_activas)) {
                $codigos = array_merge($codigos, explode(',', $ep->re_estaciones_activas));
            }
        }
        $codigos = array_unique(array_filter(array_map('trim', $codigos)));

        if (empty($codigos)) return [];

        $mediciones = DB::table(function ($query) use ($codigos) {
            $query->select(
                'rde_estacion',
                'rde_valor',
                DB::raw('ROW_NUMBER() OVER (PARTITION BY rde_estacion ORDER BY rde_hora DESC) as posicion')
            )
            ->from('umbrales_randatosepisodio')
            ->whereIn('rde_estacion', $codigos);
        }, 'subconsulta')
        ->where('posicion', 1)
        ->pluck('rde_valor', 'rde_estacion');

        $estaciones = DB::table('umbrales_umbralesran')->whereIn('ur_codigo', $codigos)->get();
        $embalses = DB::table('umbrales_embalsesran')->whereIn('er_codigo', $codigos)->get();

        $niveles = [];

        foreach ($estaciones as $est) {
            $val = $mediciones->get($est->ur_codigo);
            $niveles[$est->ur_codigo] = $this->calcularNivelDinamico($val, $est, 'ur_');
        }
        foreach ($embalses as $emb) {
            $val = $mediciones->get($emb->er_codigo);
            $niveles[$emb->er_codigo] = $this->calcularNivelDinamico($val, $emb, 'er_');
        }

        return $niveles;
    }

    public function activosGlobal()
    {
        $episodios = DB::table('umbrales_ranepisodio')
            ->leftJoin('umbrales_ccaa', 'umbrales_ranepisodio.re_ccaa_id', '=', 'umbrales_ccaa.c_id')
            ->select('umbrales_ranepisodio.*', 'umbrales_ccaa.c_comunidad_autonoma as nombre_ccaa')
            ->whereNull('re_hora_fin')
            ->orderBy('re_hora_inicio', 'desc')
            ->get();

        $nivelesEstaciones = $this->obtenerNivelesAlertaVarios($episodios);

        return view('auth.episodios_lista', [
            'episodios' => $episodios,
            'titulo' => 'Cuenca del Tajo: Episodios Activos',
            'tipo' => 'Activos',
            'nivelesEstaciones' => $nivelesEstaciones
        ]);
    }

    public function historicoGlobal()
    {
        $episodios = DB::table('umbrales_ranepisodio')
            ->leftJoin('umbrales_ccaa', 'umbrales_ranepisodio.re_ccaa_id', '=', 'umbrales_ccaa.c_id')
            ->select('umbrales_ranepisodio.*', 'umbrales_ccaa.c_comunidad_autonoma as nombre_ccaa')
            ->whereNotNull('re_hora_fin')
            ->orderBy('re_hora_inicio', 'desc')
            ->get();

        $nivelesEstaciones = $this->obtenerNivelesAlertaVarios($episodios);

        return view('auth.episodios_lista', [
            'episodios' => $episodios,
            'titulo' => 'Cuenca del Tajo: Histórico de Episodios',
            'tipo' => 'Históricos',
            'nivelesEstaciones' => $nivelesEstaciones
        ]);
    }

    // --- VISTAS POR CCAA ---
    public function activosPorCCAA($id)
    {
        $episodios = DB::table('umbrales_ranepisodio')
            ->leftJoin('umbrales_ccaa', 'umbrales_ranepisodio.re_ccaa_id', '=', 'umbrales_ccaa.c_id')
            ->select('umbrales_ranepisodio.*', 'umbrales_ccaa.c_comunidad_autonoma as nombre_ccaa')
            ->where('re_ccaa_id', $id)
            ->whereNull('re_hora_fin')
            ->orderBy('re_hora_inicio', 'desc')
            ->get();

        $ccaa = DB::table('umbrales_ccaa')->where('c_id', $id)->first();
        $nombreCcaa = $ccaa ? $ccaa->c_comunidad_autonoma : "CCAA $id";

        $nivelesEstaciones = $this->obtenerNivelesAlertaVarios($episodios);

        return view('auth.episodios_lista', [
            'episodios' => $episodios,
            'titulo' => "$nombreCcaa: Episodios Activos",
            'tipo' => 'Activos',
            'nivelesEstaciones' => $nivelesEstaciones
        ]);
    }

    public function historicoPorCCAA($id)
    {
        $episodios = DB::table('umbrales_ranepisodio')
            ->leftJoin('umbrales_ccaa', 'umbrales_ranepisodio.re_ccaa_id', '=', 'umbrales_ccaa.c_id')
            ->select('umbrales_ranepisodio.*', 'umbrales_ccaa.c_comunidad_autonoma as nombre_ccaa')
            ->where('re_ccaa_id', $id)
            ->whereNotNull('re_hora_fin')
            ->orderBy('re_hora_inicio', 'desc')
            ->get();

        $ccaa = DB::table('umbrales_ccaa')->where('c_id', $id)->first();
        $nombreCcaa = $ccaa ? $ccaa->c_comunidad_autonoma : "CCAA $id";

        $nivelesEstaciones = $this->obtenerNivelesAlertaVarios($episodios);

        return view('auth.episodios_lista', [
            'episodios' => $episodios,
            'titulo' => "$nombreCcaa: Histórico de Episodios",
            'tipo' => 'Históricos',
            'nivelesEstaciones' => $nivelesEstaciones
        ]);
    }


    // BUSCADOR
    public function buscarGlobal(Request $request, \App\Services\AlertasUmbralesService $servicioAlertas)
    {
        $query = $request->input('query');
        if (!$query) return redirect()->back();

        $resultados = $servicioAlertas->BuscarGlobal($query);

        return view('auth.busqueda_resultados', [
            'titulo' => 'Resultados de la búsqueda: "' . $query . '"',
            'embalses' => $resultados['embalses'],
            'roeas' => $resultados['roeas'],
            'marcos_control' => $resultados['marcos_control'],
            'aforos' => $resultados['aforos'],
        ]);
    }

    // Detalle de un episodio
    public function detalle($id)
    {
        $episodio = DB::table('umbrales_ranepisodio')
            ->leftJoin('umbrales_ccaa', 'umbrales_ranepisodio.re_ccaa_id', '=', 'umbrales_ccaa.c_id')
            ->select('umbrales_ranepisodio.*', 'umbrales_ccaa.c_comunidad_autonoma as nombre_ccaa')
            ->where('re_id', $id)
            ->first();
        if (!$episodio) abort(404);

        $codigosEstaciones = !empty($episodio->re_estaciones_historicas) ? explode(',', $episodio->re_estaciones_historicas) : [];
        $codigos = array_filter(array_map('trim', $codigosEstaciones));

        $estaciones = collect();

        if (count($codigos) > 0) {

            $mediciones = DB::table('umbrales_randatosepisodio')
                ->select(
                    'rde_estacion',
                    'rde_valor',
                    'rde_valor_accesorio',
                    'rde_hora',
                    DB::raw('ROW_NUMBER() OVER (PARTITION BY rde_estacion ORDER BY rde_hora DESC) as posicion')
                )
                ->where('rde_ran_episodio_id', $id)
                ->whereIn('rde_estacion', $codigos)
                ->get()
                ->groupBy('rde_estacion');

            // ROEAS / Aforos
            $ran = DB::table('umbrales_umbralesran')
                ->leftJoin('umbrales_ccaa', 'umbrales_umbralesran.ur_comunidad_autonoma_id', '=', 'umbrales_ccaa.c_id')
                ->whereIn('ur_codigo', $codigos)->get()
                ->map(function ($item) use ($mediciones, $episodio) {
                    $m = collect($mediciones->get($item->ur_codigo, []));
                    $actual = $m->where('posicion', 1)->first();
                    $anterior = $m->where('posicion', 2)->first();
                    return (object)[
                        'tipo' => 'aforo',
                        'codigo' => $item->ur_codigo,
                        'nombre' => $item->ur_nombre,
                        'rio' => $item->ur_rio,
                        'ccaa' => $item->c_comunidad_autonoma ?? '---',
                        'tag_salida' => $item->ur_tag_ip21,
                        'tag_secundario' => $item->ur_tag_ip21_caudal ?? '---',
                        'valor' => $actual ? $actual->rde_valor : null,
                        'valor_acc' => $actual ? $actual->rde_valor_accesorio : null,
                        'hora' => $actual ? Carbon::parse($actual->rde_hora)->format('d/m/Y H:i:s') : '---',
                        'nivel_alerta' => $this->calcularNivelDinamico($actual ? $actual->rde_valor : null, $item, 'ur_'),
                        'ultimo_nivel_alerta' => $this->calcularNivelDinamico($anterior ? $anterior->rde_valor : null, $item, 'ur_'),
                        'episodio_id' => $episodio->re_id,
                        'umbral1' => $item->ur_umbral1 ?? 0,
                        'umbral2' => $item->ur_umbral2 ?? 0,
                        'umbral3' => $item->ur_umbral3 ?? 0,
                    ];
                });
            // Embalses
            $embalses = DB::table('umbrales_embalsesran')
                ->leftJoin('umbrales_ccaa', 'umbrales_embalsesran.er_comunidad_autonoma_id', '=', 'umbrales_ccaa.c_id')
                ->whereIn('er_codigo', $codigos)->get()
                ->map(function ($item) use ($mediciones, $episodio) {
                    $m = collect($mediciones->get($item->er_codigo, []));
                    $actual = $m->where('posicion', 1)->first();
                    $anterior = $m->where('posicion', 2)->first();
                    return (object)[
                        'tipo' => 'embalse',
                        'codigo' => $item->er_codigo,
                        'nombre' => $item->er_nombre,
                        'rio' => $item->er_rio ?? '---',
                        'ccaa' => $item->c_comunidad_autonoma ?? '---',
                        'tag_salida' => $item->er_tag_ip21 ?? '---',
                        'tag_secundario' => $item->er_tag_volumen ?? '---',
                        'valor' => $actual ? $actual->rde_valor : null,
                        'valor_acc' => $actual ? $actual->rde_valor_accesorio : null,
                        'hora' => $actual ? Carbon::parse($actual->rde_hora)->format('d/m/Y H:i:s') : '---',
                        'nivel_alerta' => $this->calcularNivelDinamico($actual ? $actual->rde_valor : null, $item, 'er_'),
                        'ultimo_nivel_alerta' => $this->calcularNivelDinamico($anterior ? $anterior->rde_valor : null, $item, 'er_'),
                        'episodio_id' => $episodio->re_id,
                        'umbral1' => $item->er_umbral1 ?? 0,
                        'umbral2' => $item->er_umbral2 ?? 0,
                        'umbral3' => $item->er_umbral3 ?? 0,
                    ];
                });

            // Guarda todas las estaciones y lo ordena por nivel de alerta
            $estaciones = $ran->merge($embalses)->sortByDesc('nivel_alerta')->values();
        }

        $nombreEpisodio = $episodio->re_nombre ?? 'Episodio ' . $episodio->re_id;
        $ccaa = $episodio->nombre_ccaa ?? 'CCAA Desconocida';

        // Formateamos las fechas para que se vean bien (ej: "12/03/2026")
        $horaIni = $episodio->re_hora_inicio ? Carbon::parse($episodio->re_hora_inicio)->format('d/m/Y') : '---';

        $horaFinText = '';
        if (!empty($episodio->re_hora_fin)) {
            $horaFinText = ' - Fin: ' . Carbon::parse($episodio->re_hora_fin)->format('d/m/Y');
        } else {
            $horaFinText = ' - (Activo)';
        }

        // Juntamos todo en un string limpio
        $tituloDetalle = "Detalle: {$nombreEpisodio} | {$ccaa} | Inicio: {$horaIni}{$horaFinText}";

        return view('auth.episodios_detalle', [
            'episodio' => $episodio,
            'estaciones' => $estaciones,
            'titulo' => $tituloDetalle
        ]);
    }

    private function calcularNivelDinamico($valor, $estacion, $prefijo)
    {
        if ($valor === null) return 0;

        $u3 = (float)($estacion->{$prefijo . 'umbral3'} ?? 0);
        $u2 = (float)($estacion->{$prefijo . 'umbral2'} ?? 0);
        $u1 = (float)($estacion->{$prefijo . 'umbral1'} ?? 0);

        if ($u3 > 0 && $valor >= $u3) return 3;
        if ($u2 > 0 && $valor >= $u2) return 2;
        if ($u1 > 0 && $valor >= $u1) return 1;

        return 0;
    }


    // Lógica para cerrar el episodio
    public function cerrarEpisodio($id)
    {
        DB::table('umbrales_ranepisodio')
            ->where('re_id', $id)
            ->update([
                're_hora_fin' => now()
            ]);

        return redirect()->route('inicio')->with('exito', 'El episodio ha sido cerrado');
    }

    public function renombrarEpisodio(Request $request, $id)
    {
        $request->validate([
            'nuevo_nombre' => 'required|string|max:255'
        ]);

        DB::table('umbrales_ranepisodio')
            ->where('re_id', $id)
            ->update([
                're_nombre' => $request->nuevo_nombre
            ]);

        return back()->with('exito', 'Nombre del episodio actualizado correctamente.');
    }


public function mapaGlobal()
    {

        $mediciones = DB::table(function ($query) {
            $query->select(
                'rde_estacion',
                'rde_valor',
                DB::raw('ROW_NUMBER() OVER (PARTITION BY rde_estacion ORDER BY rde_hora DESC) as posicion')
            )
            ->from('umbrales_randatosepisodio');
        }, 'subconsulta')
        ->where('posicion', 1)
        ->pluck('rde_valor', 'rde_estacion');

        $estaciones = DB::table('umbrales_umbralesran')
            ->join('umbrales_coordran', DB::raw('TRIM(umbrales_umbralesran.ur_codigo)'), '=', DB::raw('TRIM(umbrales_coordran.lr_codigo_txt)'))
            ->leftJoin('umbrales_ccaa', 'umbrales_umbralesran.ur_comunidad_autonoma_id', '=', 'umbrales_ccaa.c_id')
            ->select('ur_codigo as codigo', 'ur_nombre as nombre', 'latitud', 'longitud', 'c_comunidad_autonoma as ccaa', 'ur_umbral1', 'ur_umbral2', 'ur_umbral3')
            ->whereNotNull('latitud')
            ->get()
            ->map(function($estacion) use ($mediciones) {
                $codigo = strtoupper(trim($estacion->codigo));

                if (str_starts_with($codigo, 'R')) {
                    $estacion->tipo = 'roea';
                } elseif (str_starts_with($codigo, 'M')) {
                    $estacion->tipo = 'marco';
                } else {
                    $estacion->tipo = 'aforo';
                }

                $valorReal = $mediciones->get($codigo);
                $estacion->valor_actual = $valorReal ?? 'Sin datos';
                $estacion->nivel_alerta = $this->calcularNivelDinamico($valorReal, $estacion, 'ur_');

                return $estacion;
            });

        $embalses = DB::table('umbrales_embalsesran')
            ->join('umbrales_coordran', DB::raw('TRIM(umbrales_embalsesran.er_codigo)'), '=', DB::raw('TRIM(umbrales_coordran.lr_codigo_txt)'))
            ->leftJoin('umbrales_ccaa', 'umbrales_embalsesran.er_comunidad_autonoma_id', '=', 'umbrales_ccaa.c_id')
            ->select('er_codigo as codigo', 'er_nombre as nombre', 'latitud', 'longitud', 'c_comunidad_autonoma as ccaa', 'er_umbral1', 'er_umbral2', 'er_umbral3')
            ->whereNotNull('latitud')
            ->get()
            ->map(function($embalse) use ($mediciones) {
                $embalse->tipo = 'embalse';
                $codigo = strtoupper(trim($embalse->codigo));

                $valorReal = $mediciones->get($codigo);
                $embalse->valor_actual = $valorReal ?? 'Sin datos';
                $embalse->nivel_alerta = $this->calcularNivelDinamico($valorReal, $embalse, 'er_');

                return $embalse;
            });

        $puntos = $estaciones->merge($embalses)->map(function($punto) {
            $punto->ccaa = $punto->ccaa ?? 'Sin definir';
            return $punto;
        });

        $listaCcaa = $puntos->pluck('ccaa')->unique()->sort()->values();

        return view('auth.mapa_global', [
            'titulo' => 'Mapa Global de la Cuenca',
            'puntos' => $puntos,
            'listaCcaa' => $listaCcaa
        ]);
    }
}
