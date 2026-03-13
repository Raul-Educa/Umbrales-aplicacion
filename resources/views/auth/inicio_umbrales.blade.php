@extends('auth.plantilla')

@section('contenido')
    <style>
        .tabla-hidro { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .tabla-hidro th { text-align: left; padding: 12px; border-bottom: 2px solid #dee2e6; color: #555; font-size: 0.85rem; }
        .tabla-hidro td { padding: 12px; border-bottom: 1px solid #eee; font-size: 0.85rem; }

        .fila-alerta-3 { background-color: rgba(255, 0, 0, 0.15) !important; font-weight: bold; }
        .fila-alerta-2 { background-color: rgba(255, 140, 0, 0.15) !important; }
        .fila-alerta-1 { background-color: rgba(255, 215, 0, 0.15) !important; }

        .status-dot { height: 10px; width: 10px; border-radius: 50%; display: inline-block; margin-right: 5px; }
        .dot-3 { background-color: red; box-shadow: 0 0 5px red; }
        .dot-2 { background-color: orange; }
        .dot-1 { background-color: gold; }
        .dot-0 { background-color: #bbb; }

        .pill-global { background-color: #455a64; color: white; padding: 4px 12px; border-radius: 12px; font-size: 0.85rem; font-weight: bold; }
        .val-box { display: inline-block; margin-top: 4px; font-size: 0.8rem; color: #333; }

        .grafico-fila { background: rgba(255, 255, 255, 0.5); border-radius: 4px; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s; }
        .grafico-fila:hover { transform: scale(1.05); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }

        .tabs-container { margin-bottom: 20px; border-bottom: 2px solid #dee2e6; display: flex; gap: 10px; margin-top: 20px; }
        .tab-btn { padding: 10px 20px; border: none; background: #f8f9fa; cursor: pointer; font-weight: bold; border-radius: 8px 8px 0 0; color: #666; transition: 0.3s; }
        .tab-btn.active { background: #0d6efd; color: white; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); z-index: 1000; display: none; justify-content: center; align-items: center; }
        .modal-content { background: white; padding: 20px; border-radius: 12px; width: 90%; max-width: 900px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2); position: relative; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px; }

        .btn-close { background: #ef4444; color: white; border: none; border-radius: 50%; width: 30px; height: 30px; font-weight: bold; cursor: pointer; }
        .btn-close:hover { background: #dc2626; }

        /* Botones de Filtro de Tiempo */
        .filtros-tiempo-grafico { padding: 10px 20px; background: #f8f9fa; border-bottom: 1px solid #eee; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .btn-filtro-tiempo { background: white; border: 1px solid #ced4da; padding: 4px 14px; border-radius: 15px; font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: all 0.2s; color: #495057; }
        .btn-filtro-tiempo:hover { background: #e9ecef; }
        .btn-filtro-tiempo.active { background: #0d6efd; color: white; border-color: #0d6efd; }

        /* --- ESTILOS PARA EL BOCADILLO (TOOLTIP) --- */
        .icono-info { position: relative; display: inline-block; cursor: help; color: #0d6efd; margin-left: 5px; font-weight: bold; font-size: 0.85rem; background: #e9ecef; border-radius: 50%; width: 18px; height: 18px; text-align: center; line-height: 18px; }
        .icono-info .bocadillo-texto {
            visibility: hidden;
            width: 260px; /* <--- Ancho ampliado para que quepa bien la opción 1 */
            background-color: #333;
            color: #fff; text-align: center; border-radius: 6px; padding: 8px 10px;
            position: absolute; z-index: 100; bottom: 130%; left: 50%; transform: translateX(-50%);
            opacity: 0; transition: opacity 0.3s; font-size: 0.75rem; font-weight: normal; box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        }
        .icono-info .bocadillo-texto::after { content: ""; position: absolute; top: 100%; left: 50%; margin-left: -5px; border-width: 5px; border-style: solid; border-color: #333 transparent transparent transparent; }
        .icono-info:hover .bocadillo-texto { visibility: visible; opacity: 1; }
    </style>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <h2 style="margin: 0;">{{ $titulo }}</h2>
        </div>

        <div>
            <span class="pill-global">{{ count($aforos) + count($embalses) }} Estaciones en Alerta</span>
            <small style="color: #666; margin-left: 10px;">Última actualización: {{ now()->format('H:i') }}</small>
        </div>
    </div>

    <div class="tabs-container">
        <button class="tab-btn active" onclick="openTab(event, 'tab-aforos')">Aforos en Río ({{ count($aforos) }})</button>
        <button class="tab-btn" onclick="openTab(event, 'tab-embalses')">Embalses ({{ count($embalses) }})</button>
    </div>

    {{-- CONTENIDO AFOROS --}}
    <div id="tab-aforos" class="tab-content active">
        @include('auth.tabla_inicio', ['estaciones' => $aforos, 'label_acc' => 'Caudal'])
    </div>

    {{-- CONTENIDO EMBALSES --}}
    <div id="tab-embalses" class="tab-content">
        @include('auth.tabla_inicio', ['estaciones' => $embalses, 'label_acc' => 'Volumen'])
    </div>

    {{-- MODAL DEL GRÁFICO (Con barra de filtros) --}}
    <div id="graficoModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle" style="margin: 0; color: #333;">Detalle de Estación</h3>
                <button id="closeModal" class="btn-close">X</button>
            </div>

            <div class="filtros-tiempo-grafico">
                <span style="font-size: 0.85rem; color: #666; font-weight: bold;">Rango:</span>
                <button class="btn-filtro-tiempo" data-dias="1">1 Día</button>
                <button class="btn-filtro-tiempo active" data-dias="7">7 Días</button>
                <button class="btn-filtro-tiempo" data-dias="10">10 Días</button>
                <button class="btn-filtro-tiempo" data-dias="30">30 Días</button>
                <button class="btn-filtro-tiempo" data-dias="all">Histórico</button>
            </div>

            <div class="modal-body">
                <div id="chartDetalle" style="min-height: 300px; display: flex; align-items: center; justify-content: center; color: #666;">
                    Cargando datos detallados...
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        // FUNCIÓN PARA LAS PESTAÑAS
        function openTab(evt, tabName) {
            let i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
                tabcontent[i].classList.remove("active");
            }
            tablinks = document.getElementsByClassName("tab-btn");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].classList.remove("active");
            }
            document.getElementById(tabName).style.display = "block";
            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.classList.add("active");

            // Fuerza a redibujar los gráficos para evitar problemas de tamaño oculto
            setTimeout(() => { window.dispatchEvent(new Event('resize')); }, 50);
        }

        // VARIABLES Y LÓGICA DE GRÁFICOS
        let chartAmpliado = null;
        let estacionActivaModal = null;
        window.datosEstacionesCache = {};

        document.addEventListener('DOMContentLoaded', async () => {
            const contenedores = document.querySelectorAll('.grafico-fila');
            const modal = document.getElementById('graficoModal');
            const closeModal = document.getElementById('closeModal');
            const botonesFiltro = document.querySelectorAll('.btn-filtro-tiempo');

            // Cerrar modal
            closeModal.addEventListener('click', () => modal.style.display = 'none');
            window.addEventListener('click', (e) => {
                if (e.target === modal) modal.style.display = 'none';
            });

            // Lógica de botones de tiempo
            botonesFiltro.forEach(btn => {
                btn.addEventListener('click', function() {
                    if (!estacionActivaModal) return;
                    botonesFiltro.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
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
                                let suma = 0, cant = 0;
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
                            chart: { type: 'area', height: 40, sparkline: { enabled: true }, animations: { enabled: false } },
                            stroke: { curve: 'smooth', width: 2 },
                            series: [{ name: 'Media', data: valoresMedios }],
                            colors: ['#3b82f6'],
                            tooltip: { fixed: { enabled: false }, x: { show: false }, y: { title: { formatter: () => '' } } }
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
            document.getElementById('modalTitle').innerText = `Historial Completo: Estación ${codigo}`;

            estacionActivaModal = codigo;

            document.querySelectorAll('.btn-filtro-tiempo').forEach(b => b.classList.remove('active'));
            document.querySelector('.btn-filtro-tiempo[data-dias="7"]').classList.add('active');

            cargarGraficoGrande(codigo, '7');
        }

        async function cargarGraficoGrande(codigo, dias) {
            const chartContenedor = document.getElementById('chartDetalle');

            if (chartAmpliado) {
                chartAmpliado.destroy();
                chartAmpliado = null;
            }

            chartContenedor.style.width = '100%';
            chartContenedor.style.minHeight = '350px';
            chartContenedor.innerHTML = '<span style="color:#666;">Cargando datos detallados...</span>';

            try {
                const cacheKey = `${codigo}_${dias}`;
                let json;

                if (window.datosEstacionesCache[cacheKey]) {
                    json = window.datosEstacionesCache[cacheKey];
                } else {
                    const url = `{{ url('/api/historial') }}/${codigo}?dias=${dias}`;
                    const res = await fetch(url);
                    json = await res.json();
                    window.datosEstacionesCache[cacheKey] = json;
                }

                if (!json || !json.fechas || json.fechas.length === 0) {
                    chartContenedor.innerHTML = '<span style="color:red">No hay datos para este rango de tiempo.</span>';
                    return;
                }

                chartContenedor.innerHTML = '';

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
                            timestamp = new Date(fecha[2], fecha[1] - 1, fecha[0], hora[0], hora[1], hora[2] || 0).getTime();
                        } else {
                            timestamp = new Date(str.replace(/-/g, '/').replace(' ', 'T')).getTime();
                        }
                    }
                    let valorEjeY = json.valores[i] === null ? null : parseFloat(json.valores[i]);
                    return { x: timestamp, y: valorEjeY };
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
                            style: { color: '#fff', background: u.color, fontWeight: 'bold', padding: { left: 5, right: 5, top: 2, bottom: 2 } },
                            text: `${u.texto} (> ${u.valor})`
                        }
                    });
                }

                const options = {
                    chart: { type: 'area', height: 350, animations: { enabled: false }, zoom: { enabled: true } },
                    series: [{ name: 'Valor Registrado', data: datosMapeados }],
                    annotations: { yaxis: zonasUmbrales },
                    stroke: { curve: 'straight', width: 2 },
                    colors: ['#0d6efd'],
                    fill: { type: 'gradient', gradient: { opacityFrom: 0.6, opacityTo: 0.1 } },
                    dataLabels: { enabled: false },
                    markers: { size: 0, hover: { size: 5 } },
                    xaxis: { type: 'datetime', labels: { datetimeUTC: false, format: 'dd/MM HH:mm' } },
                    yaxis: {
                        title: { text: 'Valor Registrado' },
                        labels: { formatter: function(val) { return (val === null || val === undefined) ? '' : Number(val).toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); } }
                    },
                    tooltip: {
                        x: { format: 'dd/MM/yyyy HH:mm' },
                        y: { formatter: function(val) { return (val === null || val === undefined) ? '---' : Number(val).toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); } }
                    },
                    grid: { borderColor: '#f1f1f1', xaxis: { lines: { show: true } }, yaxis: { lines: { show: true } } }
                };

                chartAmpliado = new ApexCharts(chartContenedor, options);
                chartAmpliado.render();

            } catch (err) {
                chartContenedor.innerHTML = '<span style="color:red">Error al cargar la gráfica.</span>';
            }
        }
    </script>
@endsection
