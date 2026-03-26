@extends('auth.plantilla')

@section('contenido')
    <style>
        .tabla-hidro {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .tabla-hidro th {
            text-align: left;
            padding: 12px;
            border-bottom: 2px solid #dee2e6;
            color: #555;
            font-size: 0.85rem;
        }

        .tabla-hidro td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 0.85rem;
        }

        .fila-alerta-3 {
            background-color: rgba(255, 0, 0, 0.15) !important;
            font-weight: bold;
        }

        .fila-alerta-2 {
            background-color: rgba(255, 140, 0, 0.15) !important;
        }

        .fila-alerta-1 {
            background-color: rgba(255, 215, 0, 0.15) !important;
        }

        .status-dot {
            height: 10px;
            width: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        .dot-3 {
            background-color: red;
            box-shadow: 0 0 5px red;
        }

        .dot-2 {
            background-color: orange;
        }

        .dot-1 {
            background-color: gold;
        }

        .dot-0 {
            background-color: #bbb;
        }

        .pill-global {
            background-color: #455a64;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: bold;
        }

        .val-box {
            display: inline-block;
            margin-top: 4px;
            font-size: 0.8rem;
            color: #333;
        }

        .grafico-fila {
            background: rgba(255, 255, 255, 0.5);
            border-radius: 4px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .grafico-fila:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .modal-overlay {
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

        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 12px;
            width: 90%;
            max-width: 900px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .btn-close {
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-close:hover {
            background: #dc2626;
        }

        .btn-volver {
            background: #6c757d;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.85rem;
        }

        .btn-volver:hover {
            background: #5a6268;
        }

        .btn-admin-ghost {
            background: transparent;
            border: 1px solid #ced4da;
            color: #495057;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-admin-ghost:hover {
            background: #f8f9fa;
            border-color: #adb5bd;
        }

        .btn-admin-ghost.danger {
            border-color: #dc3545;
            color: #dc3545;
        }

        .btn-admin-ghost.danger:hover {
            background: #fff5f5;
        }
        .filtros-tiempo-grafico {
            padding: 10px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        .btn-filtro-tiempo {
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
        .btn-filtro-tiempo:hover {
            background: #e9ecef;
        }
        .btn-filtro-tiempo.active {
            background: #2563eb;
            color: white;
            border-color: #2563eb;
        }
    </style>

    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px;">

        <div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <a href="javascript:history.back()" class="btn-volver">← Volver</a>
                <h2 style="margin: 0;">{{ $titulo }}</h2>

                @if (session('is_superuser'))
                    <div style="display: flex; gap: 8px; margin-left: 10px;">
                        <button onclick="configuracionAdmin('admin-form-renombrar')" class="btn-admin-ghost">
                            Renombrar
                        </button>

                        @if (is_null($episodio->re_hora_fin))
                            <button onclick="configuracionAdmin('admin-form-cerrar')" class="btn-admin-ghost danger">
                                Cerrar Episodio
                            </button>
                        @endif
                    </div>
                @endif
            </div>

            @if (session('is_superuser'))
                <div id="admin-form-renombrar" style="display: none; margin-top: 15px; padding-left: 85px;">
                    <form action="{{ route('episodio.renombrar', $episodio->re_id) }}" method="POST"
                        onsubmit="return confirm('¿Confirmas el cambio de nombre?');"
                        style="display: flex; gap: 10px; align-items: center;">
                        @csrf
                        <input type="text" name="nuevo_nombre" value="{{ $episodio->re_nombre }}" required
                            style="padding: 5px 10px; border: 1px solid #ced4da; border-radius: 4px; font-size: 0.85rem; width: 250px;">

                        <button type="button" onclick="configuracionAdmin('admin-form-renombrar')"
                            style="background: transparent; border: 1px solid #ced4da; color: #6c757d; padding: 5px 12px; border-radius: 4px; font-size: 0.85rem; cursor: pointer; transition: 0.2s;">Cancelar</button>

                        <button type="submit"
                            style="background: #28a745; color: white; border: none; padding: 5px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: bold; cursor: pointer;">Guardar</button>
                    </form>
                </div>

                @if (is_null($episodio->re_hora_fin))
                    <div id="admin-form-cerrar" style="display: none; margin-top: 15px; padding-left: 85px;">
                        <form action="{{ route('episodio.cerrar', $episodio->re_id) }}" method="POST"
                            onsubmit="return confirm('ULTIMO AVISO: ¿Estas seguro de que quieres terminar este episodio y moverlo al histórico?');"
                            style="display: flex; gap: 15px; align-items: center;">
                            @csrf
                            <span style="color: #dc3545; font-size: 0.85rem;"><strong>Atención:</strong> Seguro que quieres cerrar el episodio {{ $episodio->re_id }}?</span>

                            <button type="button" onclick="configuracionAdmin('admin-form-cerrar')"
                                style="background: transparent; border: 1px solid #ced4da; color: #6c757d; padding: 5px 12px; border-radius: 4px; font-size: 0.85rem; cursor: pointer; transition: 0.2s;">Cancelar</button>

                            <button type="submit"
                                style="background: #dc3545; color: white; border: none; padding: 5px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: bold; cursor: pointer;">Sí, Cerrar Ahora</button>
                        </form>
                    </div>
                @endif

                <script>
                    function configuracionAdmin(idObjeto) {
                        const panelSeleccionado = document.getElementById(idObjeto);
                        const todosLosPaneles = ['admin-form-renombrar', 'admin-form-cerrar'];

                        todosLosPaneles.forEach(id => {
                            const panel = document.getElementById(id);
                            if (panel && id !== idObjeto) panel.style.display = 'none';
                        });

                        if (panelSeleccionado) {
                            panelSeleccionado.style.display = panelSeleccionado.style.display === 'block' ? 'none' : 'block';
                        }
                    }
                </script>
            @endif
        </div>

        <div style="text-align: right; margin-top: 5px;">
            <span class="pill-global">{{ count($estaciones) }} Estaciones Afectadas</span>
            <div style="color: #666; font-size: 0.75rem; margin-top: 6px;">Última act: {{ now()->format('H:i') }}</div>
        </div>
    </div>

    <table class="tabla-hidro">
        <thead>
            <tr>
                <th>Código / Nombre</th>
                <th>Río</th>
                <th>TagReferencia / Valor</th>
                <th>TagCaudal / Caudal o Vol.</th>
                <th>Límites (Umbrales)</th>
                <th>Estado Actual</th>
                <th>Fecha/Hora</th>
                <th style="width: 150px;">Gráfico</th>
            </tr>
        </thead>
        <tbody>
            @forelse($estaciones as $e)
                <tr class="fila-alerta-{{ $e->nivel_alerta }}">
                    <td>
                        <strong>{{ $e->codigo ?? '---' }}</strong><br>
                        {{ $e->nombre }}
                    </td>
                    <td>{{ $e->rio ?? '---' }}</td>

                    <td>
                        {{ $e->tag_salida ?? '---' }}<br>
                        <span class="val-box">
                            Valor:
                            <strong>{{ is_numeric($e->valor) ? number_format((float) $e->valor, 3, ',', '.') : $e->valor ?? '---' }}</strong>
                        </span>
                    </td>

                    <td>
                        {{ $e->tag_secundario ?? '---' }}<br>
                        <span class="val-box">
                            Valor:
                            <strong>{{ isset($e->valor_acc) && is_numeric($e->valor_acc) ? number_format((float) $e->valor_acc, 3, ',', '.') : $e->valor_acc ?? '---' }}</strong>
                        </span>
                    </td>
                    <td style="font-size: 0.8rem; line-height: 1.4;">
                        @if ((float) $e->umbral1 > 0)
                            <span style="color:gold"><b>A:</b>
                                >{{ number_format((float) $e->umbral1, 2, ',', '.') }}</span><br>
                        @endif
                        @if ((float) $e->umbral2 > 0)
                            <span style="color: orange"><b>N:</b>
                                >{{ number_format((float) $e->umbral2, 2, ',', '.') }}</span><br>
                        @endif
                        @if ((float) $e->umbral3 > 0)
                            <span style="color: red"><b>R:</b>
                                >{{ number_format((float) $e->umbral3, 2, ',', '.') }}</span>
                        @endif
                        @if ((float) $e->umbral1 == 0 && (float) $e->umbral2 == 0 && (float) $e->umbral3 == 0)
                            <span style="color: #999;">Sin definir</span>
                        @endif
                    </td>

                    {{-- Estado Actual --}}
                    <td>
                        <span class="status-dot dot-{{ $e->nivel_alerta }}"></span>
                        <b>{{ $e->nivel_alerta == 3 ? 'ALERTA 3' : ($e->nivel_alerta == 2 ? 'ALERTA 2' : ($e->nivel_alerta == 1 ? 'ALERTA 1' : 'NORMAL')) }}</b>
                    </td>

                    <td>{{ $e->hora ?? '---' }}</td>

                    {{-- Gráfico --}}
                    <td>
                        <div class="grafico-fila" data-codigo="{{ $e->codigo }}"
                            style="width: 150px; height: 40px; display: flex; align-items: center; justify-content: center;">
                            <small style="color: #999; font-size: 10px;">Cargando...</small>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px; color: #999;">
                        No hay estaciones asociadas a este episodio.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

   {{-- MODAL DEL GRÁFICO --}}
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
                <div id="chartDetalle"
                    style="min-height: 300px; display: flex; align-items: center; justify-content: center; color: #666;">
                    Cargando datos detallados...
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        // Variables para el grafico grande y guardar en caché
        let chartAmpliado = null;
        let estacionActivaModal = null;
        window.datosEstacionesCache = {};

        document.addEventListener('DOMContentLoaded', async () => {
            const contenedores = document.querySelectorAll('.grafico-fila');
            const modal = document.getElementById('graficoModal');
            const closeModal = document.getElementById('closeModal');
            const botonesFiltro = document.querySelectorAll('.btn-filtro-tiempo');

            //Cerrar el grafico
            closeModal.addEventListener('click', () => modal.style.display = 'none');
            window.addEventListener('click', (e) => {
                if (e.target === modal) modal.style.display = 'none';
            });


            botonesFiltro.forEach(btn => {
                btn.addEventListener('click', function() {
                    if (!estacionActivaModal) return;

                    // Cambiar el estado activo del botón
                    botonesFiltro.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    const dias = this.dataset.dias;
                    cargarGraficoGrande(estacionActivaModal, dias);
                });
            });

            for (const div of contenedores) {
                const codigo = div.dataset.codigo;
                if (!codigo || codigo === '---') continue;

                try {
                    // Pide 7 dias
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

                        // Al hacer clic, abrimos la ventana gigante
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

// Logica para cargar el gáfico grande
        async function cargarGraficoGrande(codigo, dias) {
            const chartContenedor = document.getElementById('chartDetalle');

            // Si ya había un gráfico grande abierto, lo borramos para pintar el nuevo
            if (chartAmpliado) {
                chartAmpliado.destroy();
                chartAmpliado = null;
            }

            chartContenedor.style.width = '100%';
            chartContenedor.style.minHeight = '350px';
            chartContenedor.innerHTML = '<span style="color:#666;">Cargando datos detallados...</span>';

            try {
                // Revisa la cache para no pedir datos si ya los tenemos
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

                // Umbrales
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

                // Pintar el gráfico grande con los días elegidos
                const options = {
                    chart: { type: 'area', height: 350, animations: { enabled: false }, zoom: { enabled: true } },
                    series: [{ name: 'Valor Registrado', data: datosMapeados }],
                    annotations: { yaxis: zonasUmbrales },
                    stroke: { curve: 'straight', width: 2 },
                    colors: ['#2563eb'],
                    fill: { type: 'gradient', gradient: { opacityFrom: 0.6, opacityTo: 0.1 } },
                    dataLabels: { enabled: false },
                    markers: { size: 0, hover: { size: 5 } },
                    xaxis: { type: 'datetime', labels: { datetimeUTC: false, format: 'dd/MM HH:mm' } },
                    yaxis: {
                        title: { text: 'Tag Referencia' },
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
