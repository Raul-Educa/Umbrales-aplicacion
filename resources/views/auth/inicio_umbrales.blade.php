@extends('auth.plantilla')

@section('contenido')
    <style>
        .tablaHidro {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .tablaHidro th {
            text-align: left;
            padding: 12px;
            border-bottom: 2px solid #dee2e6;
            color: #555;
            font-size: 0.85rem;
        }

        .tablaHidro td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 0.85rem;
        }

        .filaAlerta3 {
            background-color: rgba(255, 0, 0, 0.15) !important;
            font-weight: bold;
        }

        .filaAlerta2 {
            background-color: rgba(255, 140, 0, 0.15) !important;
        }

        .filaAlerta1 {
            background-color: rgba(255, 215, 0, 0.15) !important;
        }

        .puntEstado {
            height: 10px;
            width: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        .punt3 {
            background-color: red;
            box-shadow: 0 0 5px red;
        }

        .punt2 {
            background-color: orange;
        }

        .punt1 {
            background-color: gold;
        }

        .punt0 {
            background-color: #bbb;
        }

        .etiquetaGlobal {
            background-color: #455a64;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: bold;
        }

        .cajaValor {
            display: inline-block;
            margin-top: 4px;
            font-size: 0.8rem;
            color: #333;
        }

        .filaGrafico {
            background: rgba(255, 255, 255, 0.5);
            border-radius: 4px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .filaGrafico:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .contenedorPestanas {
            margin-bottom: 20px;
            border-bottom: 2px solid #dee2e6;
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .botonPestana {
            padding: 10px 20px;
            border: none;
            background: #f8f9fa;
            cursor: pointer;
            font-weight: bold;
            border-radius: 8px 8px 0 0;
            color: #666;
            transition: 0.3s;
        }

        .botonPestana.activo {
            background: #0d6efd;
            color: white;
        }

        .contenidoPestana {
            display: none;
        }

        .contenidoPestana.activo {
            display: block;
        }

        .capaModal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1000;
            display: none;
            justify-content: center;
            align-items: center;
        }

        .contenidoModal {
            background: white;
            padding: 20px;
            border-radius: 12px;
            width: 90%;
            max-width: 900px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .cabeceraModal {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .botonCerrarModal {
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            font-weight: bold;
            cursor: pointer;
        }

        .botonCerrarModal:hover {
            background: #dc2626;
        }

        /* Botones de Filtro de Tiempo */
        .filtrosTiempoGrafico {
            padding: 10px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .botonFiltroTiempo {
            background: white;
            border: 1px solid #ced4da;
            padding: 4px 14px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            color: #495057;
        }

        .botonFiltroTiempo:hover {
            background: #e9ecef;
        }

        .botonFiltroTiempo.activo {
            background: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }

        /* --- Estilos de Información --- */
        .iconoInfo {
            position: relative;
            display: inline-block;
            cursor: help;
            color: #0d6efd;
            margin-left: 5px;
            font-weight: bold;
            font-size: 0.85rem;
            background: #e9ecef;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            text-align: center;
            line-height: 18px;
        }

        .iconoInfo .bocadilloTexto {
            visibility: hidden;
            width: 260px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 8px 10px;
            position: absolute;
            z-index: 100;
            bottom: 130%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 0.75rem;
            font-weight: normal;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }

        .iconoInfo .bocadilloTexto::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #333 transparent transparent transparent;
        }

        .iconoInfo:hover .bocadilloTexto {
            visibility: visible;
            opacity: 1;
        }
    </style>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <h2 style="margin: 0;">{{ $titulo }}</h2>
        </div>

        <div>
            <span class="etiquetaGlobal">{{ count($aforos) + count($embalses) }} Estaciones en Alerta</span>
            <small style="color: #666; margin-left: 10px;">Última actualización: {{ now()->format('H:i') }}</small>
        </div>
    </div>

    <div class="contenedorPestanas">
        <button class="botonPestana activo" onclick="abrirPestana(event, 'pestanaAforos')">Aforos en Río
            ({{ count($aforos) }})</button>
        <button class="botonPestana" onclick="abrirPestana(event, 'pestanaEmbalses')">Embalses
            ({{ count($embalses) }})</button>
    </div>

    {{-- CONTENIDO AFOROS --}}
    <div id="pestanaAforos" class="contenidoPestana activo">
        @include('auth.tabla_inicio', ['estaciones' => $aforos, 'label_acc' => 'Caudal'])
    </div>

    {{-- CONTENIDO EMBALSES --}}
    <div id="pestanaEmbalses" class="contenidoPestana">
        @include('auth.tabla_inicio', ['estaciones' => $embalses, 'label_acc' => 'Volumen'])
    </div>

    {{-- MODAL DEL GRÁFICO --}}
    <div id="graficoModal" class="capaModal">
        <div class="contenidoModal">
            <div class="cabeceraModal">
                <h3 id="tituloModal" style="margin: 0; color: #333;">Detalle de Estación</h3>
                <button id="cerrarModal" class="botonCerrarModal">X</button>
            </div>

            <div class="filtrosTiempoGrafico">
                <span style="font-size: 0.85rem; color: #666; font-weight: bold;">Rango:</span>
                <button class="botonFiltroTiempo" data-dias="1">1 Día</button>
                <button class="botonFiltroTiempo activo" data-dias="7">7 Días</button>
                <button class="botonFiltroTiempo" data-dias="10">10 Días</button>
                <button class="botonFiltroTiempo" data-dias="30">30 Días</button>
                <button class="botonFiltroTiempo" data-dias="all">Histórico</button>
            </div>

            <div class="cuerpoModal">
                <div id="graficoDetalle"
                    style="min-height: 300px; display: flex; align-items: center; justify-content: center; color: #666;">
                    Cargando datos detallados...
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        // FUNCIÓN PARA LAS PESTAÑAS
        function abrirPestana(evt, nombrePestana) {
            let i, contenidoPestana, enlacesPestana;
            contenidoPestana = document.getElementsByClassName("contenidoPestana");
            for (i = 0; i < contenidoPestana.length; i++) {
                contenidoPestana[i].style.display = "none";
                contenidoPestana[i].classList.remove("activo");
            }
            enlacesPestana = document.getElementsByClassName("botonPestana");
            for (i = 0; i < enlacesPestana.length; i++) {
                enlacesPestana[i].classList.remove("activo");
            }
            document.getElementById(nombrePestana).style.display = "block";
            document.getElementById(nombrePestana).classList.add("activo");
            evt.currentTarget.classList.add("activo");

            setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
            }, 50);
        }

        // VARIABLES Y LÓGICA DE GRÁFICOS
        let graficoAmpliado = null;
        let estacionActivaModal = null;
        window.datosEstacionesCache = {};

        document.addEventListener('DOMContentLoaded', async () => {
            const contenedores = document.querySelectorAll('.filaGrafico');
            const modal = document.getElementById('graficoModal');
            const cerrarModal = document.getElementById('cerrarModal');
            const botonesFiltro = document.querySelectorAll('.botonFiltroTiempo');

            // Cerrar modal
            cerrarModal.addEventListener('click', () => modal.style.display = 'none');
            window.addEventListener('click', (e) => {
                if (e.target === modal) modal.style.display = 'none';
            });

            // Lógica de botones de tiempo
            botonesFiltro.forEach(btn => {
                btn.addEventListener('click', function() {
                    if (!estacionActivaModal) return;
                    botonesFiltro.forEach(b => b.classList.remove('activo'));
                    this.classList.add('activo');
                    const dias = this.dataset.dias;
                    cargarGraficoGrande(estacionActivaModal, dias);
                });
            });

            // Pintar minigráficos de la tabla
            for (const div of contenedores) {
                const codigo = div.dataset.codigo;
                if (!codigo || codigo === '---') continue;

                try {
                    const url = `{{ url('/api/historial') }}/${codigo}?dias=7`;
                    const res = await fetch(url);
                    const json = await res.json();

                    if (json.valores && json.valores.length > 0) {
                        window.datosEstacionesCache[`${codigo}_7`] = json;

                        const maxPuntos = 40;
                        let valoresMedios = [];
                        if (json.valores.length > maxPuntos) {
                            const bloque = Math.ceil(json.valores.length / maxPuntos);
                            for (let i = 0; i < json.valores.length; i += bloque) {
                                let suma = 0,
                                    cant = 0;
                                for (let j = 0; j < bloque && (i + j) < json.valores.length; j++) {
                                    if (json.valores[i + j] != null) {
                                        suma += parseFloat(json.valores[i + j]);
                                        cant++;
                                    }
                                }
                                valoresMedios.push(cant > 0 ? (suma / cant) : null);
                            }
                        } else {
                            valoresMedios = json.valores;
                        }

                        div.innerHTML = '';
                        new ApexCharts(div, {
                            chart: {
                                type: 'area',
                                height: 40,
                                sparkline: {
                                    enabled: true
                                },
                                animations: {
                                    enabled: false
                                }
                            },
                            stroke: {
                                curve: 'smooth',
                                width: 2
                            },
                            series: [{
                                name: 'Media',
                                data: valoresMedios
                            }],
                            colors: ['#3b82f6'],
                            tooltip: {
                                fixed: {
                                    enabled: false
                                },
                                x: {
                                    show: false
                                },
                                y: {
                                    title: {
                                        formatter: () => ''
                                    }
                                }
                            }
                        }).render();

                        div.onclick = () => abrirModalGigante(codigo);
                    } else {
                        div.innerHTML = '<small style="color:#ccc; font-size:9px;">Sin datos</small>';
                    }
                } catch (err) {
                    div.innerHTML = '<small style="color:red; font-size:9px;">Error</small>';
                }
            }
        });

        function abrirModalGigante(codigo) {
            const modal = document.getElementById('graficoModal');
            modal.style.display = 'flex';
            document.getElementById('tituloModal').innerText = `Historial Completo: Estación ${codigo}`;

            estacionActivaModal = codigo;

            document.querySelectorAll('.botonFiltroTiempo').forEach(b => b.classList.remove('activo'));
            document.querySelector('.botonFiltroTiempo[data-dias="7"]').classList.add('activo');

            cargarGraficoGrande(codigo, '7');
        }

        async function cargarGraficoGrande(codigo, dias) {
            const contenedorGrafico = document.getElementById('graficoDetalle');

            if (graficoAmpliado) {
                graficoAmpliado.destroy();
                graficoAmpliado = null;
            }

            contenedorGrafico.style.width = '100%';
            contenedorGrafico.style.minHeight = '350px';
            contenedorGrafico.innerHTML = '<span style="color:#666;">Cargando datos detallados...</span>';

            try {
                const claveCache = `${codigo}_${dias}`;
                let json;

                if (window.datosEstacionesCache[claveCache]) {
                    json = window.datosEstacionesCache[claveCache];
                } else {
                    const url = `{{ url('/api/historial') }}/${codigo}?dias=${dias}`;
                    const res = await fetch(url);
                    json = await res.json();
                    window.datosEstacionesCache[claveCache] = json;
                }

                if (!json || !json.fechas || json.fechas.length === 0) {
                    contenedorGrafico.innerHTML =
                        '<span style="color:red">No hay datos para este rango de tiempo.</span>';
                    return;
                }

                contenedorGrafico.innerHTML = '';

                const datosMapeados = json.fechas.map((f, i) => {
                    let timestamp;
                    if (!isNaN(f) && f !== null && f !== '') {
                        timestamp = Number(f);
                    } else {
                        let str = String(f).trim();
                        if (str.includes('/')) {
                            let partes = str.split(' ');
                            let fecha = partes[0].split('/');
                            let hora = (partes[1] || '00:00:00').split(':');
                            timestamp = new Date(fecha[2], fecha[1] - 1, fecha[0], hora[0], hora[1], hora[2] ||
                                0).getTime();
                        } else {
                            timestamp = new Date(str.replace(/-/g, '/').replace(' ', 'T')).getTime();
                        }
                    }
                    let valorEjeY = json.valores[i] === null ? null : parseFloat(json.valores[i]);
                    return {
                        x: timestamp,
                        y: valorEjeY
                    };
                });

                const umbrales = json.umbrales || [];
                umbrales.sort((a, b) => a.valor - b.valor);
                const zonasUmbrales = [];
                for (let i = 0; i < umbrales.length; i++) {
                    const u = umbrales[i];
                    const valorSig = umbrales[i + 1] ? umbrales[i + 1].valor : 99999;
                    zonasUmbrales.push({
                        y: u.valor,
                        y2: valorSig,
                        fillColor: u.color,
                        opacity: 0.15,
                        label: {
                            borderColor: u.color,
                            style: {
                                color: '#fff',
                                background: u.color,
                                fontWeight: 'bold',
                                padding: {
                                    left: 5,
                                    right: 5,
                                    top: 2,
                                    bottom: 2
                                }
                            },
                            text: `${u.texto} (> ${u.valor})`
                        }
                    });
                }

                const opciones = {
                    chart: {
                        type: 'area',
                        height: 350,
                        animations: {
                            enabled: false
                        },
                        zoom: {
                            enabled: true
                        }
                    },
                    series: [{
                        name: 'Valor Registrado',
                        data: datosMapeados
                    }],
                    annotations: {
                        yaxis: zonasUmbrales
                    },
                    stroke: {
                        curve: 'straight',
                        width: 2
                    },
                    colors: ['#0d6efd'],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            opacityFrom: 0.6,
                            opacityTo: 0.1
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    markers: {
                        size: 0,
                        hover: {
                            size: 5
                        }
                    },
                    xaxis: {
                        type: 'datetime',
                        labels: {
                            datetimeUTC: false,
                            format: 'dd/MM HH:mm'
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Valor Registrado'
                        },
                        labels: {
                            formatter: function(val) {
                                return (val === null || val === undefined) ? '' : Number(val).toLocaleString(
                                    'es-ES', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                            }
                        }
                    },
                    tooltip: {
                        x: {
                            format: 'dd/MM/yyyy HH:mm'
                        },
                        y: {
                            formatter: function(val) {
                                return (val === null || val === undefined) ? '---' : Number(val).toLocaleString(
                                    'es-ES', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                            }
                        }
                    },
                    grid: {
                        borderColor: '#f1f1f1',
                        xaxis: {
                            lines: {
                                show: true
                            }
                        },
                        yaxis: {
                            lines: {
                                show: true
                            }
                        }
                    }
                };

                graficoAmpliado = new ApexCharts(contenedorGrafico, opciones);
                graficoAmpliado.render();

            } catch (err) {
                contenedorGrafico.innerHTML = '<span style="color:red">Error al cargar la gráfica.</span>';
            }
        }
    </script>
@endsection
