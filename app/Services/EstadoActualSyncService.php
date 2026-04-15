<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class EstadoActualSyncService
{
    /**
     * * MÉTODO DE SINCRONIZACIÓN
     * * Se encarga de descargar todo, filtrarlo y guardarlo en Caché
     */
    public function sincronizarCaches(): void
    {
        // * 1. Obtenemos todas las estaciones de la BBDD
        $estacionesConfiguradas = $this->obtenerEstacionesEstadoActual();

        // * 2. Cruzamos BBDD con la API para obtener el estado real
        $estadoActualCalculado = $this->construirEstadoActual($estacionesConfiguradas);

        // ! Filtro para que solo tenga alerta (Nivel > 0)
        $estacionesEnAlerta = $estadoActualCalculado
            ->filter(fn($estacionCalculada) => $estacionCalculada['alerta'] > 0)
            ->values();

        // * 4. Preparar y guardar Caché GLOBAL (Toda la cuenca agrupada por CCAA)
        $alertasGlobalesPorComunidad = $estacionesEnAlerta
            ->groupBy('comunidad')
            ->map(fn($grupoComunidad) => $grupoComunidad->sortByDesc('alerta')->values())
            ->sortKeys();

        Cache::forever('api_estado_actual_global', $alertasGlobalesPorComunidad);

        // * 5. Preparar y guardar Caché INDIVIDUAL (Por el ID de cada CCAA)
        $alertasPorIdComunidad = $estacionesEnAlerta
            ->groupBy('comunidad_id')
            ->map(fn($grupoComunidad) => $grupoComunidad->sortByDesc('alerta')->values());

        $idsComunidades = DB::table('umbrales_ccaa')->pluck('c_id');

        // ? Aunque una CCAA no tenga alertas, se guarda un array vacío collect() para evitar fallos en la vista
        foreach ($idsComunidades as $idComunidad) {
            Cache::forever(
                'api_estado_actual_ccaa_' . (int) $idComunidad,
                $alertasPorIdComunidad->get((int) $idComunidad, collect())->values()
            );
        }

        // * Guardamos la hora exacta de la última sincronización
        Cache::forever('api_estado_actual_sync_at', now()->toDateTimeString());
    }

    /**
     * * =========================================================================
     * * EXTRACCIÓN DE BBDD
     * * Carga aforos y embalses activos junto con sus umbrales
     * * =========================================================================
     */
    public function obtenerEstacionesEstadoActual(?int $ccaaId = null)
    {
        // * Consulta 1: Ríos / Aforos
        $consultaAforos = DB::table('umbrales_umbralesran')
            ->join('umbrales_ccaa', 'umbrales_umbralesran.ur_comunidad_autonoma_id', '=', 'umbrales_ccaa.c_id')
            ->select(
                DB::raw("'AFORO' as tipo"),
                'umbrales_umbralesran.ur_codigo as codigo',
                'umbrales_umbralesran.ur_nombre as nombre',
                'umbrales_umbralesran.ur_provincia as provincia',
                'umbrales_umbralesran.ur_tag_ip21 as tag_ip21',
                'umbrales_umbralesran.ur_umbral1 as nivel1',
                'umbrales_umbralesran.ur_umbral2 as nivel2',
                'umbrales_umbralesran.ur_umbral3 as nivel3',
                'umbrales_ccaa.c_id as comunidad_id',
                'umbrales_ccaa.c_comunidad_autonoma as nombre_comunidad'
            )
            ->where('umbrales_umbralesran.ur_activo', 1);

        // * Consulta 2: Embalses
        $consultaEmbalses = DB::table('umbrales_embalsesran')
            ->join('umbrales_ccaa', 'umbrales_embalsesran.er_comunidad_autonoma_id', '=', 'umbrales_ccaa.c_id')
            ->select(
                DB::raw("'EMBALSE' as tipo"),
                'umbrales_embalsesran.er_codigo as codigo',
                'umbrales_embalsesran.er_nombre as nombre',
                'umbrales_embalsesran.er_provincia as provincia',
                'umbrales_embalsesran.er_tag_ip21 as tag_ip21',
                'umbrales_embalsesran.er_umbral1 as nivel1',
                'umbrales_embalsesran.er_umbral2 as nivel2',
                'umbrales_embalsesran.er_umbral3 as nivel3',
                'umbrales_ccaa.c_id as comunidad_id',
                'umbrales_ccaa.c_comunidad_autonoma as nombre_comunidad'
            )
            ->where('umbrales_embalsesran.er_activo', 1);

        // ? Si el método recibe un ID, aplica el WHERE a ambas consultas
        if (!is_null($ccaaId)) {
            $consultaAforos->where('umbrales_umbralesran.ur_comunidad_autonoma_id', $ccaaId);
            $consultaEmbalses->where('umbrales_embalsesran.er_comunidad_autonoma_id', $ccaaId);
        }

        // * Fusionamos ambas tablas en una sola colección
        return $consultaAforos->get()->merge($consultaEmbalses->get());
    }

    /**
     * * CONSTRUCTOR DEL ESTADO FINAL
     * * Une la base de datos con los resultados de la API
     */
    public function construirEstadoActual($datosEstaciones)
    {
        // * Pide los datos reales a la API de SAIH
        $lecturasPorEstacion = $this->obtenerLecturasApiPorEstacion($datosEstaciones);

        $estacionesCalculadas = collect();

        foreach ($datosEstaciones as $estacionBase) {
            $claveUnicaEstacion = $this->claveEstacion((string) $estacionBase->tipo, (string) $estacionBase->codigo);
            $codigoEstacion = (string) $estacionBase->codigo;

            // ? Si la API falló para esta estación, generamos un array "vacío" de seguridad por defecto
            $lecturaEstacion = $lecturasPorEstacion[$claveUnicaEstacion] ?? [
                'valor' => null,
                'fecha' => 'Sin conexión',
                'json'  => [],
                'tag'   => trim((string) ($estacionBase->tag_ip21 ?? '')) !== ''
                    ? (string) $estacionBase->tag_ip21
                    : $codigoEstacion . 'LI__02',
            ];

            $valorLeido = $lecturaEstacion['valor'];
            $jsonLectura = $lecturaEstacion['json'];

            // * Ensamblamos el Array definitivo para guardar en Caché
            $estacionesCalculadas->push([
                'tipo'         => $estacionBase->tipo,
                'codigo'       => $estacionBase->codigo,
                'nombre'       => $estacionBase->nombre,
                'provincia'    => $estacionBase->provincia,
                'estacion'     => $jsonLectura['estacion'] ?? $estacionBase->codigo,
                'senal'        => $jsonLectura['senal'] ?? $lecturaEstacion['tag'],
                'valor'        => $valorLeido,
                'fecha'        => $lecturaEstacion['fecha'],
                'alerta'       => $this->calcularNivelAlerta(
                    $valorLeido,
                    $estacionBase->nivel1,
                    $estacionBase->nivel2,
                    $estacionBase->nivel3
                ),
                'comunidad_id' => (int) $estacionBase->comunidad_id,
                'comunidad'    => strtoupper((string) $estacionBase->nombre_comunidad),
                'nivel1'       => (float) ($estacionBase->nivel1 ?? 0),
                'nivel2'       => (float) ($estacionBase->nivel2 ?? 0),
                'nivel3'       => (float) ($estacionBase->nivel3 ?? 0),
            ]);
        }

        return $estacionesCalculadas;
    }

    /**
     * * CONEXIÓN A LA API EXTERNA */
    private function obtenerLecturasApiPorEstacion($estaciones): array
    {
        $estacionesColeccion = collect($estaciones)->values();

        if ($estacionesColeccion->isEmpty()) {
            return [];
        }

        $lecturasPorClave = [];

        foreach ($estacionesColeccion as $estacionBase) {
            $codigoEstacion = (string) $estacionBase->codigo;
            $tipoEstacion = strtoupper((string) $estacionBase->tipo);
            $tagConfigurado = trim((string) ($estacionBase->tag_ip21 ?? ''));
            $claveUnicaEstacion = $this->claveEstacion((string) $estacionBase->tipo, $codigoEstacion);

            // ? Tag por defecto si la base de datos no lo tiene configurado
            $tagDefecto = $tagConfigurado !== '' ? $tagConfigurado : ($codigoEstacion . 'LI__02');

            $lecturaResuelta = [
                'valor' => null,
                'fecha' => 'Sin conexión',
                'json'  => [],
                'tag'   => $tagDefecto,
            ];

            if ($tipoEstacion === 'EMBALSE') {
                $lecturasPorClave[$claveUnicaEstacion] = $lecturaResuelta;
                continue;
            }


            $tags = array_values(array_filter(array_unique([
                $tagConfigurado,
                $codigoEstacion . 'LI__01',
                $codigoEstacion . 'LI__02',
            ])));

            foreach ($tags as $tag) {
                // * Se usa Http::retry para evitar bloqueos si la red tiene micro-cortes
                $urlApiSaih = env('API_SAIH_URL', 'http://vcmas08:8001/tr/ultimo_valor_tag/');
                $respuesta = Http::retry(4, 300, null, false)
                    ->connectTimeout(1)
                    ->timeout(8)
                    ->get($urlApiSaih . '?tag=' . urlencode($tag));
                if (!($respuesta instanceof Response) || !$respuesta->ok()) {
                    continue; // ! Si la petición falla, pasa al siguiente candidato o estación
                }

                $json = $respuesta->json();
                $valor = isset($json['valor']) && is_numeric($json['valor']) ? (float) $json['valor'] : null;

                // ? Si encontramos un valor válido, cortamos el bucle y guardamos
                if ($valor !== null) {
                    $lecturaResuelta = [
                        'valor' => $valor,
                        'fecha' => $json['fecha'] ?? 'Sin conexión',
                        'json'  => $json,
                        'tag'   => $tag,
                    ];
                    break;
                }
            }

            $lecturasPorClave[$claveUnicaEstacion] = $lecturaResuelta;
        }

        return $lecturasPorClave;
    }



    private function claveEstacion(string $tipo, string $codigo): string
    {
        return strtoupper($tipo) . '|' . strtoupper($codigo);
    }

    /**
     */
    private function calcularNivelAlerta(?float $valorSensor, $nivel1, $nivel2, $nivel3): int
    {
        if ($valorSensor === null) {
             // ? Si no devuelve nada o algo no válido, se pondra en alarma 0 para no alertar sin sentido
             return 0;
        }

        $u1 = (float) ($nivel1 ?? 0);
        $u2 = (float) ($nivel2 ?? 0);
        $u3 = (float) ($nivel3 ?? 0);

        if ($u3 > 0 && $valorSensor >= $u3) {
            return 3;
        }
        if ($u2 > 0 && $valorSensor >= $u2) {
            return 2;
        }
        if ($u1 > 0 && $valorSensor >= $u1) {
            return 1;
        }

        return 0;
    }
}
