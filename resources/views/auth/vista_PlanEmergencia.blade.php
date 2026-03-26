@extends('auth.plantilla')



@section('contenido')
    <style>
        .matriz-container {
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



        .fila-ccaa {
            background: #e9ecef;
            font-weight: bold;
            border-top: 2px solid #666;
        }

        .fila-provincia {
            background: #fff;
        }



        .col-nombre {
            width: 220px;
            text-align: left;
            padding-left: 10px;
            position: sticky;
            left: 0;
            background: inherit;
            z-index: 5;
            border-right: 2px solid #666 !important;
        }

        .indent {
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

        .rastro-h::after,
        .rastro-v::after {
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

        .rastro-h::after {
            box-shadow: inset 0 2px 0 rgba(0, 0, 0, 0.6), inset 0 -2px 0 rgba(0, 0, 0, 0.6);
        }

        .rastro-v::after {
            box-shadow: inset 2px 0 0 rgba(0, 0, 0, 0.6), inset -2px 0 0 rgba(0, 0, 0, 0.6);
        }

        .celda-foco {
            box-shadow: inset 0 0 0 3px #000 !important;
            filter: brightness(1.1);
            z-index: 10 !important;
            cursor: crosshair;
        }

        .header-foco {
            background-color: #333 !important;
            color: #fff !important;
            font-weight: bold;
            box-shadow: inset 0 0 0 2px #000;
            transform: scale(1.1);
            z-index: 10;
            position: relative;
        }



        /* Estilos del Modal y Celdas Clicables */

        .celda-click {
            cursor: pointer !important;
        }

        .celda-click:hover {
            filter: brightness(0.8) !important;
            box-shadow: inset 0 0 0 3px #fff !important;
        }

        .modal-oculto {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
        }

        .modal-contenido {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 25px;
            border: 1px solid #888;
            width: 450px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            font-family: sans-serif;
        }

        .cerrar-modal {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .cerrar-modal:hover {
            color: black;
        }

        .modal-subtitulo {
            color: #666;
            font-size: 0.9em;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .modal-caja-desc {
            background: #f9f9f9;
            padding: 15px;
            border-left: 4px solid #333;
            margin-top: 15px;
            border-radius: 4px;
        }

        .btn-pdf {
            display: inline-block;
            background-color: #d9534f;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .btn-pdf:hover {
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



    <div class="matriz-container">
        <table class="matriz">

            <thead>

                <tr>

                    <th class="col-nombre" rowspan="2">ZONA / PROVINCIA</th>

                    @foreach ($meses as $m)
                        <th colspan="{{ $m->daysInMonth }}">{{ strtoupper($m->translatedFormat('F Y')) }}</th>
                    @endforeach

                </tr>

                <tr>

                    @foreach ($meses as $m)
                        @for ($d = 1; $d <= $m->daysInMonth; $d++)
                            <th>{{ $d }}</th>
                        @endfor
                    @endforeach

                </tr>

            </thead>

            <tbody>

                @foreach ($ccaa as $c)
                    @php

                        $esSimple =
                            $c->c_comunidad_autonoma == 'Madrid' ||
                            $c->c_comunidad_autonoma == 'Portugal' ||
                            $c->c_comunidad_autonoma == 'Ayto. Madrid';

                    @endphp



                    <tr class="fila-ccaa">

                        <td class="col-nombre">

                            {{ strtoupper($c->c_comunidad_autonoma) }}

                            @if ($c->plan_emergencia)
                                <span style="color: #000000;">

                                    {{ '|  ' . $c->plan_emergencia }}

                                </span>
                            @endif

                        </td>

                        @foreach ($meses as $m)
                            @for ($d = 1; $d <= $m->daysInMonth; $d++)
                                @php
                                    $fecha = $m->copy()->day($d)->format('Y-m-d');
                                    $info = $matrizColores[$c->c_id]['global'][$fecha] ?? null;

                                    $nivel = $info ? $info['nivel'] : 0;

                                    $clase = $info ? 'c' . $nivel . ' celda-click' : '';

                                    if (isset($info['cambio']) && $info['cambio']) {
                                        $clase .= ' marcadorCambio';
                                    }
                                @endphp

                                <td class='{{ $clase }}' data-fecha="{{ $fecha }}"
                                    data-zona="{{ $c->c_comunidad_autonoma }}" data-hora="{{ $info['hora'] ?? '' }}"
                                    data-desc="{{ $info['desc'] ?? 'No hay descripción disponible' }}"
                                    data-pdf="{{ $info['pdf'] ?? '' }}" data-nivel="{{ $nivel }}"
                                    data-num-eventos="{{ $info['num_eventos'] ?? 0 }}">
                                </td>
                            @endfor
                        @endforeach

                    </tr>



                    @if (!$esSimple)
                        @foreach ($c->provincias as $p)
                            <tr class="fila-provincia">

                                <td class="col-nombre indent">{{ $p->p_provincia }}</td>

                                @foreach ($meses as $m)
                                    @for ($d = 1; $d <= $m->daysInMonth; $d++)
                                        @php
                                            $fecha = $m->copy()->day($d)->format('Y-m-d');
                                            $info = $matrizColores[$c->c_id][$p->p_id][$fecha] ?? null;

                                            $nivel = $info ? $info['nivel'] : 0;

                                            $clase = $info ? 'c' . $nivel . ' celda-click' : '';

                                            if (isset($info['cambio']) && $info['cambio']) {
                                                $clase .= ' marcadorCambio';
                                            }
                                        @endphp

                                        <td class='{{ $clase }}' data-fecha="{{ $fecha }}"
                                            data-zona="{{ $p->p_provincia }}" data-hora="{{ $info['hora'] ?? '' }}"
                                            data-desc="{{ $info['desc'] ?? 'No hay descripción disponible' }}"
                                            data-pdf="{{ $info['pdf'] ?? '' }}" data-nivel="{{ $nivel }}"
                                            data-num-eventos="{{ $info['num_eventos'] ?? 0 }}">
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



    <div id="modalDetalles" class="modal-oculto">

        <div class="modal-contenido">

            <span class="cerrar-modal" onclick="cerrarModal()">&times;</span>

            <h2 id="modal-zona">ZONA</h2>

            <p class="modal-subtitulo">Fecha: <span id="modal-fecha"></span> | Hora de la situación: <span
                    id="modal-hora"></span></p>



            <div class="modal-caja-desc">

                <strong>Descripción de la situación:</strong>

                <p id="modal-desc"></p>

            </div>



            <div id="modal-caja-pdf" style="margin-top: 20px; text-align: center;">

                <a id="btn-descargar-pdf" href="#" target="_blank" class="btn-pdf">

                    Ver / Descargar Documento Oficial

                </a>

            </div>

        </div>

    </div>



    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const tabla = document.querySelector('.matriz');

            const tbody = tabla.querySelector('tbody');

            const theadDias = tabla.querySelector('thead tr:nth-child(2)');



            // Lógica de Coordenadas

            tabla.addEventListener('mouseover', function(e) {

                if (e.target.tagName === 'TD' && e.target.closest('tbody')) {

                    const celdaActual = e.target;

                    const filaActual = celdaActual.parentElement;



                    document.querySelectorAll('.rastro-h, .rastro-v, .celda-foco, .header-foco').forEach(
                        el => {

                            el.classList.remove('rastro-h', 'rastro-v', 'celda-foco', 'header-foco');

                        });



                    const colIndex = Array.from(filaActual.children).indexOf(celdaActual);

                    const rowIndex = Array.from(tbody.children).indexOf(filaActual);



                    celdaActual.classList.add('celda-foco');



                    for (let j = 0; j < colIndex; j++) {

                        filaActual.children[j].classList.add('rastro-h');

                    }



                    const filas = tbody.children;

                    for (let i = 0; i < rowIndex; i++) {

                        if (filas[i].children[colIndex]) {

                            filas[i].children[colIndex].classList.add('rastro-v');

                        }

                    }



                    if (colIndex > 0) {

                        const thDia = theadDias.children[colIndex - 1];

                        if (thDia) thDia.classList.add('header-foco');

                    }

                }

            });



            tabla.addEventListener('mouseleave', function() {

                document.querySelectorAll('.rastro-h, .rastro-v, .celda-foco, .header-foco').forEach(el => {

                    el.classList.remove('rastro-h', 'rastro-v', 'celda-foco', 'header-foco');

                });

            });



           document.addEventListener('click', function(e) {
                const td = e.target.closest('.celda-click');

                if (td) {
                    document.getElementById('modal-zona').innerText = td.dataset.zona;
                    document.getElementById('modal-fecha').innerText = td.dataset.fecha;
                    document.getElementById('modal-hora').innerText = td.dataset.hora || '--:--';
                    document.getElementById('modal-desc').innerHTML = td.dataset.desc;

                    const btnPdf = document.getElementById('btn-descargar-pdf');
                    const cajaPdf = document.getElementById('modal-caja-pdf');

                    if (td.dataset.pdf) {
                        btnPdf.href = '/storage/' + td.dataset.pdf;
                        cajaPdf.style.display = 'block';
                    } else {
                        cajaPdf.style.display = 'none';
                    }

                    document.getElementById('modalDetalles').style.display = 'block';
                }
            });

        });



        function cerrarModal() {

            document.getElementById('modalDetalles').style.display = 'none';

        }



        window.onclick = function(event) {

            if (event.target == document.getElementById('modalDetalles')) {

                cerrarModal();

            }

        }
    </script>
@endsection
