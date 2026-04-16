@extends('auth.plantilla')

@section('contenido')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />

    <style>
        #mapa-tajo { width: 100%; height: 70vh; border-radius: 6px; border: 1px solid #ced4da; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 1; }

        .panel-filtros { background: white; padding: 15px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #e2e8f0; display: flex; gap: 15px; flex-wrap: wrap; align-items: center; }
        .filtro-control { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; background: #f8fafc; color: #334155; font-size: 0.9rem; flex-grow: 1; min-width: 150px; }
        .filtro-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }

        /* Buscador de texto */
        .buscador-texto-container { flex-grow: 2; position: relative; }
        .buscador-texto-container i { position: absolute; left: 12px; top: 12px; color: #94a3b8; }
        .buscador-texto { padding-left: 35px !important; width: 100%; }

        /* Marcadores y Popups */
        .custom-div-icon { background: transparent; border: none; }
        .marker-pin { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 14px; box-shadow: 0 3px 6px rgba(0,0,0,0.4); border: 2px solid white; transition: all 0.2s;}
        .marker-pin i { filter: drop-shadow(0 1px 1px rgba(0,0,0,0.3)); }

        .bg-alerta-0 { background-color: #10b981; }
        .bg-alerta-1 { background-color: #facc15; color: #1e293b; }
        .bg-alerta-2 { background-color: #f97316; }
        .bg-alerta-3 { background-color: #ef4444; animation: parpadeo-peligro 2s infinite; }

        @keyframes parpadeo-peligro { 0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); } 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); } }

        .leaflet-popup-content-wrapper { border-radius: 6px; padding: 0; overflow: hidden; }
        .popup-header { background: #f8fafc; padding: 12px 15px; border-bottom: 1px solid #e2e8f0; font-weight: bold; color: #1e293b; font-size: 14px;}
        .popup-body { padding: 12px 15px; font-size: 13px; color: #475569; }
        .popup-dato-destacado { background: #f1f5f9; padding: 8px; border-radius: 6px; margin-top: 8px; font-weight: 600; text-align: center; border: 1px solid #e2e8f0; color: #0f172a; }
    </style>

    <div class="container-fluid mb-4 mt-3">
        <h3 style="color: #475569; font-weight: 600; margin-bottom: 15px;"> {{ $titulo }}</h3>


        <div class="panel-filtros">

            <div class="buscador-texto-container">
                <i class="fas fa-search"></i>
                <input type="text" id="filtro-texto" class="filtro-control buscador-texto" placeholder="Buscar por nombre o código (ej: AR01, Bolarque)...">
            </div>

            <select id="filtro-tipo" class="filtro-control">
                <option value="todos">Todos los Tipos</option>
                <option value="embalse">Embalses</option>
                <option value="aforo">Aforos en Río</option>
                <option value="roea">ROEAS</option>
                <option value="marco">Marcos de Control</option>
            </select>

            <select id="filtro-ccaa" class="filtro-control">
                <option value="todas">Todas las CCAA</option>
                @foreach($listaCcaa as $comunidad)
                    <option value="{{ $comunidad }}">{{ $comunidad }}</option>
                @endforeach
            </select>

            <select id="filtro-alerta" class="filtro-control">
                <option value="todos">Cualquier Estado</option>
                <option value="0"> Normalidad</option>
                <option value="1"> Alerta Amarilla</option>
                <option value="2"> Alerta Naranja</option>
                <option value="3"> Alerta Roja</option>
            </select>
        </div>

        <div id="mapa-tajo"></div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var map = L.map('mapa-tajo').setView([39.8628, -4.0273], 7);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18 }).addTo(map);

            var marcadoresCluster = L.markerClusterGroup({ chunkedLoading: true });
            var todosLosPuntos = @json($puntos);

            function dibujarMapa(puntosA_Pintar) {
                marcadoresCluster.clearLayers();

                puntosA_Pintar.forEach(function(punto) {
                    if(!punto.latitud || !punto.longitud) return;

                    var lat = parseFloat(punto.latitud.toString().replace(',', '.'));
                    var lng = parseFloat(punto.longitud.toString().replace(',', '.'));

                    if (!isNaN(lat) && !isNaN(lng)) {

                        var iconoHtml = '<i class="fas fa-map-marker-alt"></i>';
                        if(punto.tipo === 'embalse') iconoHtml = '<i class="fas fa-water"></i>';
                        if(punto.tipo === 'aforo') iconoHtml = '<i class="fas fa-ruler-vertical"></i>';
                        if(punto.tipo === 'roea') iconoHtml = '<i class="fas fa-broadcast-tower"></i>';
                        if(punto.tipo === 'marco') iconoHtml = '<i class="fas fa-bullseye"></i>';

                        var claseColor = 'bg-alerta-' + punto.nivel_alerta;

                        var iconoPersonalizado = L.divIcon({
                            className: 'custom-div-icon',
                            html: `<div class="marker-pin ${claseColor}">${iconoHtml}</div>`,
                            iconSize: [32, 32],
                            iconAnchor: [16, 16]
                        });

                        var marcador = L.marker([lat, lng], { icon: iconoPersonalizado });

                        // valor real
                        var unidad = punto.tipo === 'embalse' ? 'hm³' : 'm³/s';
                        var contenidoPopup = `
                            <div class="popup-header">${punto.nombre}</div>
                            <div class="popup-body">
                                <div style="margin-bottom: 4px;"><strong>Código:</strong> ${punto.codigo}</div>
                                <div style="margin-bottom: 4px;"><strong>Tipo:</strong> ${punto.tipo.toUpperCase()}</div>
                                <div style="margin-bottom: 4px;"><strong>CCAA:</strong> ${punto.ccaa}</div>

                                <div class="popup-dato-destacado">
                                    Valor actual: <span style="color:#2563eb; font-size:1.1em;">${punto.valor_actual} ${unidad}</span>
                                </div>
                            </div>
                        `;

                        marcador.bindPopup(contenidoPopup);
                        marcadoresCluster.addLayer(marcador);
                    }
                });
                map.addLayer(marcadoresCluster);
            }

            dibujarMapa(todosLosPuntos);

            // Filtros para el mapa
            function aplicarFiltros() {
                var textoFiltro = document.getElementById('filtro-texto').value.toLowerCase().trim();
                var tipoFiltro = document.getElementById('filtro-tipo').value;
                var ccaaFiltro = document.getElementById('filtro-ccaa').value;
                var alertaFiltro = document.getElementById('filtro-alerta').value;

                var puntosFiltrados = todosLosPuntos.filter(function(p) {
                    var coincideTexto = true;
                    if(textoFiltro !== "") {
                        coincideTexto = p.nombre.toLowerCase().includes(textoFiltro) || p.codigo.toLowerCase().includes(textoFiltro);
                    }

                    var coincideTipo = (tipoFiltro === 'todos' || p.tipo === tipoFiltro);
                    var coincideCcaa = (ccaaFiltro === 'todas' || p.ccaa === ccaaFiltro);
                    var coincideAlerta = (alertaFiltro === 'todos' || p.nivel_alerta.toString() === alertaFiltro);

                    return coincideTexto && coincideTipo && coincideCcaa && coincideAlerta;
                });

                dibujarMapa(puntosFiltrados);
            }

            document.getElementById('filtro-tipo').addEventListener('change', aplicarFiltros);
            document.getElementById('filtro-ccaa').addEventListener('change', aplicarFiltros);
            document.getElementById('filtro-alerta').addEventListener('change', aplicarFiltros);

            document.getElementById('filtro-texto').addEventListener('keyup', aplicarFiltros);
        });
    </script>
@endsection
