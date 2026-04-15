@extends('auth.plantilla')



@section('contenido')
    <style>
        .matrizContainer {
            overflow-x: auto;
            border: 2px solid #333;
            margin-top: 20px;
        }

        .matriz {
            border-collapse: collapse;
            width: 100%;
            font-family: sans-serif;
            font-size: 12px;
            table-layout: fixed;
        }

        .matriz th,
        .matriz td {
            border: 1px solid #ccc;
            height: 30px;
            text-align: center;
        }



        .filaComunidad {
            background: #e9ecef;
            font-weight: bold;
            border-top: 2px solid #666;
        }

        .filaProvincia {
            background: #fff;
        }



        .colNombre {
            width: 220px;
            text-align: left;
            padding-left: 10px;
            position: sticky;
            left: 0;
            background: inherit;
            z-index: 5;
            border-right: 2px solid #666 !important;
        }

        .sangriaProvincia {
            padding-left: 30px !important;
            color: #444;
        }




        :root {

            --n0: #ffffff;
            /* Normalidad*/

            --n1: #eeee7b;
            /* Preemergencia / Alerta */

            --n2: #ffff00;
            /* Situación 0 */

            --n3: #ffbf00;
            /* Situación 1 */

            --n4: #FF0000;
            /* Situación 2 */

            --n5: #000000;
            /* Situación 3 */

        }



        .c0 {
            background-color: var(--n0);
        }

        .c1 {
            background-color: var(--n1);
            color: #000;
        }

        .c2 {
            background-color: var(--n2);
            color: #000;
        }

        .c3 {
            background-color: var(--n3);
            color: #000;
        }

        .c4 {
            background-color: var(--n4);
            color: #fff;
        }

        .c5 {
            background-color: var(--n5);
            color: #fff;
        }



        /* Efectos Foco y Coordenadas */

        .matriz tbody td {
            position: relative;
        }

        .rastroHorizontal::after,
        .rastroVertical::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.15);
            pointer-events: none;
            z-index: 5;
        }

        .rastroHorizontal::after {
            box-shadow: inset 0 2px 0 rgba(0, 0, 0, 0.6), inset 0 -2px 0 rgba(0, 0, 0, 0.6);
        }

        .rastroVertical::after {
            box-shadow: inset 2px 0 0 rgba(0, 0, 0, 0.6), inset -2px 0 0 rgba(0, 0, 0, 0.6);
        }

        .celdaFoco {
            box-shadow: inset 0 0 0 3px #000 !important;
            filter: brightness(1.1);
            z-index: 10 !important;
            cursor: crosshair;
        }

        .encabezadoFoco {
            background-color: #333 !important;
            color: #fff !important;
            font-weight: bold;
            box-shadow: inset 0 0 0 2px #000;
            transform: scale(1.1);
            z-index: 10;
            position: relative;
        }



        /* Estilos del Modal y Celdas Clicables */

        .celdaClicable {
            cursor: pointer !important;
        }

        .celdaClicable:hover {
            filter: brightness(0.8) !important;
            box-shadow: inset 0 0 0 3px #fff !important;
        }

        .modalOculto {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
        }

        .modalContenido {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 25px;
            border: 1px solid #888;
            width: 450px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            font-family: sans-serif;
        }

        .cerrarModalBoton {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .cerrarModalBoton:hover {
            color: black;
        }

        .modalSubtitulo {
            color: #666;
            font-size: 0.9em;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .modalCajaDescripcion {
            background: #f9f9f9;
            padding: 15px;
            border-left: 4px solid #333;
            margin-top: 15px;
            border-radius: 4px;
        }

        .botonPdf {
            display: inline-block;
            background-color: #d9534f;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .botonPdf:hover {
            background-color: #c9302c;
            color: white;
        }

        .marcadorCambio::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 0 12px 12px 0;
            border-color: transparent #374151 transparent transparent;
            z-index: 2;
        }

        .cajaDescripcionModal {
            background: #f9f9f9;
            padding: 15px;
            border-left: 4px solid #333;
            margin-top: 15px;
            border-radius: 4px;
            max-height: 250px;
            overflow-y: auto;
        }
    </style>

    <div class="matrizContainer">
        <table class="matriz">

            <thead>

                <tr>

                    <th class="colNombre" rowspan="2">ZONA / PROVINCIA</th>

                    @foreach ($meses as $mes)
                        @php
                            $mesCarbon = null;
                            if ($mes instanceof \Carbon\CarbonInterface) {
                                $mesCarbon = $mes;
                            } elseif ($mes instanceof \DateTimeInterface) {
                                $mesCarbon = \Carbon\Carbon::instance($mes);
                            } elseif (is_string($mes)) {
                                $marcaTiempoMes = strtotime($mes);
                                if ($marcaTiempoMes !== false) {
                                    $mesCarbon = \Carbon\Carbon::createFromTimestamp($marcaTiempoMes);
                                }
                            }

                            $diasDelMes = $mesCarbon ? $mesCarbon->daysInMonth : 1;
                            $tituloMes = $mesCarbon
                                ? strtoupper($mesCarbon->translatedFormat('F Y'))
                                : 'FECHA NO DISPONIBLE';
                        @endphp
                        <th colspan="{{ $diasDelMes }}">{{ $tituloMes }}</th>
                    @endforeach

                </tr>

                <tr>

                    @foreach ($meses as $mes)
                        @php
                            $mesCarbon = null;
                            if ($mes instanceof \Carbon\CarbonInterface) {
                                $mesCarbon = $mes;
                            } elseif ($mes instanceof \DateTimeInterface) {
                                $mesCarbon = \Carbon\Carbon::instance($mes);
                            } elseif (is_string($mes)) {
                                $marcaTiempoMes = strtotime($mes);
                                if ($marcaTiempoMes !== false) {
                                    $mesCarbon = \Carbon\Carbon::createFromTimestamp($marcaTiempoMes);
                                }
                            }

                            $diasDelMes = $mesCarbon ? $mesCarbon->daysInMonth : 1;
                        @endphp
                        @for ($dia = 1; $dia <= $diasDelMes; $dia++)
                            <th>{{ $dia }}</th>
                        @endfor
                    @endforeach

                </tr>

            </thead>

            <tbody>

                @foreach ($ccaa as $comunidad)
                    @php

                        $esComunidadSimple =
                            $comunidad->c_comunidad_autonoma == 'Madrid' ||
                            $comunidad->c_comunidad_autonoma == 'Portugal' ||
                            $comunidad->c_comunidad_autonoma == 'Ayto. Madrid';

                    @endphp

                    <tr class="filaComunidad">

                        <td class="colNombre">

                            {{ strtoupper($comunidad->c_comunidad_autonoma) }}

                            @if ($comunidad->plan_emergencia)
                                <span style="color: #000000;">

                                    {{ '|  ' . $comunidad->plan_emergencia }}

                                </span>
                            @endif
                        </td>
                        @foreach ($meses as $mes)
                            @php
                                $mesCarbon = null;
                                if ($mes instanceof \Carbon\CarbonInterface) {
                                    $mesCarbon = $mes;
                                } elseif ($mes instanceof \DateTimeInterface) {
                                    $mesCarbon = \Carbon\Carbon::instance($mes);
                                } elseif (is_string($mes)) {
                                    $marcaTiempoMes = strtotime($mes);
                                    if ($marcaTiempoMes !== false) {
                                        $mesCarbon = \Carbon\Carbon::createFromTimestamp($marcaTiempoMes);
                                    }
                                }

                                $diasDelMes = $mesCarbon ? $mesCarbon->daysInMonth : 1;
                            @endphp
                            @for ($dia = 1; $dia <= $diasDelMes; $dia++)
                                @php
                                    $fechaDia = $mesCarbon
                                        ? $mesCarbon->copy()->day($dia)->format('Y-m-d')
                                        : 'fecha-no-disponible';
                                    $informacionCelda = $matrizColores[$comunidad->c_id]['global'][$fechaDia] ?? null;

                                    $nivelAlerta = $informacionCelda ? $informacionCelda['nivel'] : 0;

                                    $claseCelda = $informacionCelda ? 'c' . $nivelAlerta . ' celdaClicable' : '';

                                    if (isset($informacionCelda['cambio']) && $informacionCelda['cambio']) {
                                        $claseCelda .= ' marcadorCambio';
                                    }
                                @endphp

                                <td class='{{ $claseCelda }}' data-fecha="{{ $fechaDia }}"
                                    data-zona="{{ $comunidad->c_comunidad_autonoma }}"
                                    data-hora="{{ $informacionCelda['hora'] ?? '' }}"
                                    data-desc="{{ $informacionCelda['desc'] ?? 'No hay descripción disponible' }}"
                                    data-pdf="{{ $informacionCelda['pdf'] ?? '' }}" data-nivel="{{ $nivelAlerta }}"
                                    data-num-eventos="{{ $informacionCelda['num_eventos'] ?? 0 }}">
                                </td>
                            @endfor
                        @endforeach

                    </tr>

                    @if (!$esComunidadSimple)
                        @foreach ($comunidad->provincias as $provincia)
                            <tr class="filaProvincia">

                                <td class="colNombre sangriaProvincia">{{ $provincia->p_provincia }}</td>

                                @foreach ($meses as $mes)
                                    @php
                                        $mesCarbon = null;
                                        if ($mes instanceof \Carbon\CarbonInterface) {
                                            $mesCarbon = $mes;
                                        } elseif ($mes instanceof \DateTimeInterface) {
                                            $mesCarbon = \Carbon\Carbon::instance($mes);
                                        } elseif (is_string($mes)) {
                                            $marcaTiempoMes = strtotime($mes);
                                            if ($marcaTiempoMes !== false) {
                                                $mesCarbon = \Carbon\Carbon::createFromTimestamp($marcaTiempoMes);
                                            }
                                        }

                                        $diasDelMes = $mesCarbon ? $mesCarbon->daysInMonth : 1;
                                    @endphp
                                    @for ($dia = 1; $dia <= $diasDelMes; $dia++)
                                        @php
                                            $fechaDia = $mesCarbon
                                                ? $mesCarbon->copy()->day($dia)->format('Y-m-d')
                                                : 'fecha-no-disponible';
                                            $informacionCelda =
                                                $matrizColores[$comunidad->c_id][$provincia->p_id][$fechaDia] ?? null;

                                            $nivelAlerta = $informacionCelda ? $informacionCelda['nivel'] : 0;

                                            $claseCelda = $informacionCelda
                                                ? 'c' . $nivelAlerta . ' celdaClicable'
                                                : '';

                                            if (isset($informacionCelda['cambio']) && $informacionCelda['cambio']) {
                                                $claseCelda .= ' marcadorCambio';
                                            }
                                        @endphp

                                        <td class='{{ $claseCelda }}' data-fecha="{{ $fechaDia }}"
                                            data-zona="{{ $provincia->p_provincia }}"
                                            data-hora="{{ $informacionCelda['hora'] ?? '' }}"
                                            data-desc="{{ $informacionCelda['desc'] ?? 'No hay descripción disponible' }}"
                                            data-pdf="{{ $informacionCelda['pdf'] ?? '' }}"
                                            data-nivel="{{ $nivelAlerta }}"
                                            data-num-eventos="{{ $informacionCelda['num_eventos'] ?? 0 }}">
                                        </td>
                                    @endfor
                                @endforeach

                            </tr>
                        @endforeach
                    @endif
                @endforeach

            </tbody>

        </table>

    </div>

    <div id="modalDetalles" class="modalOculto">

        <div class="modalContenido">

            <span class="cerrarModalBoton" onclick="cerrarModal()">&times;</span>

            <h2 id="modalZona">ZONA</h2>

            <p class="modalSubtitulo">Fecha: <span id="modalFecha"></span> | Hora de la situación: <span
                    id="modalHora"></span></p>

            <div class="modalCajaDescripcion">

                <strong>Descripción de la situación:</strong>

                <p id="modalDescripcion"></p>

            </div>

            <div id="modalCajaPdf" style="margin-top: 20px; text-align: center;">

                <a id="botonDescargarPdf" href="#" target="_blank" class="botonPdf">

                    Ver / Descargar Documento Oficial

                </a>

            </div>

        </div>

    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const tablaMatriz = document.querySelector('.matriz');

            const cuerpoTabla = tablaMatriz.querySelector('tbody');

            const encabezadoDias = tablaMatriz.querySelector('thead tr:nth-child(2)');

            // Lógica de Coordenadas
            tablaMatriz.addEventListener('mouseover', function(evento) {

                if (evento.target.tagName === 'TD' && evento.target.closest('tbody')) {

                    const celdaActual = evento.target;

                    const filaActual = celdaActual.parentElement;



                    document.querySelectorAll(
                        '.rastroHorizontal, .rastroVertical, .celdaFoco, .encabezadoFoco').forEach(
                        elemento => {

                            elemento.classList.remove('rastroHorizontal', 'rastroVertical', 'celdaFoco',
                                'encabezadoFoco');

                        });
                    const indiceColumna = Array.from(filaActual.children).indexOf(celdaActual);

                    const indiceFila = Array.from(cuerpoTabla.children).indexOf(filaActual);

                    celdaActual.classList.add('celdaFoco');

                    for (let indice = 0; indice < indiceColumna; indice++) {

                        filaActual.children[indice].classList.add('rastroHorizontal');

                    }

                    const filasCuerpo = cuerpoTabla.children;

                    for (let indice = 0; indice < indiceFila; indice++) {

                        if (filasCuerpo[indice].children[indiceColumna]) {

                            filasCuerpo[indice].children[indiceColumna].classList.add('rastroVertical');

                        }

                    }

                    if (indiceColumna > 0) {

                        const encabezadoDia = encabezadoDias.children[indiceColumna - 1];

                        if (encabezadoDia) encabezadoDia.classList.add('encabezadoFoco');

                    }

                }

            });

            tablaMatriz.addEventListener('mouseleave', function() {

                document.querySelectorAll('.rastroHorizontal, .rastroVertical, .celdaFoco, .encabezadoFoco')
                    .forEach(elemento => {

                        elemento.classList.remove('rastroHorizontal', 'rastroVertical', 'celdaFoco',
                            'encabezadoFoco');

                    });

            });

            document.addEventListener('click', function(evento) {
                const celdaClicable = evento.target.closest('.celdaClicable');

                if (celdaClicable) {
                    document.getElementById('modalZona').innerText = celdaClicable.dataset.zona;
                    document.getElementById('modalFecha').innerText = celdaClicable.dataset.fecha;
                    document.getElementById('modalHora').innerText = celdaClicable.dataset.hora || '--:--';
                    document.getElementById('modalDescripcion').innerHTML = celdaClicable.dataset.desc;

                    const botonPdf = document.getElementById('botonDescargarPdf');
                    const contenedorPdf = document.getElementById('modalCajaPdf');

                    if (celdaClicable.dataset.pdf) {
                        botonPdf.href = '/storage/' + celdaClicable.dataset.pdf;
                        contenedorPdf.style.display = 'block';
                    } else {
                        contenedorPdf.style.display = 'none';
                    }

                    document.getElementById('modalDetalles').style.display = 'block';
                }
            });

        });

        function cerrarModal() {

            document.getElementById('modalDetalles').style.display = 'none';

        }

        window.onclick = function(evento) {

            if (evento.target === document.getElementById('modalDetalles')) {

                cerrarModal();

            }
        }
    </script>
@endsection
