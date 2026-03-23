<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Estación {{ $codigo }}</title>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            padding: 40px;
        }

        #contenedorGrafico {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            max-width: 950px;
            margin: auto;
        }

        #contenedorSelector {
            text-align: center;
            margin-bottom: 15px;
        }

        #sinDatos {
            text-align: center;
            color: #ef4444;
            font-weight: bold;
            display: none;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

    <div id="contenedorGrafico">
        <h2 style="text-align: center;">Historial de Estación: <span style="color: #3b82f6;">{{ $codigo }}</span>
        </h2>

        <div id="contenedorSelector">
            <label for="rango">Seleccionar rango:</label>
            <select id="rango">
                <option value="7">Últimos 7 días</option>
                <option value="30">Últimos 30 días</option>
                <option value="365" selected>Último año</option>
            </select>
        </div>

        <div id="sinDatos">No hay datos disponibles para este rango.</div>
        <div id="grafico"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const selector = document.getElementById('rango');
            const divSinDatos = document.getElementById('sinDatos');
            let grafico;

            async function cargarGrafico() {
                const dias = selector.value;
                const url = "{{ route('api.grafico', ['codigo' => $codigo]) }}?dias=" + dias;

                try {
                    const res = await fetch(url);
                    const json = await res.json();

                    if (!json.fechas.length || !json.valores.length) {
                        if (grafico) grafico.updateSeries([{
                            data: []
                        }, {
                            data: []
                        }]);
                        divSinDatos.style.display = 'block';
                        return;
                    }
                    divSinDatos.style.display = 'none';

                    // Datos para ApexCharts
                    const datosArea = json.fechas.map((f, i) => ({
                        x: f,
                        y: json.valores[i]
                    }));

                    // Línea de umbral, aquí ejemplo fijo en 75 (puedes cambiar o sacar de la BD)
                    const umbral = datosArea.map(d => ({
                        x: d.x,
                        y: 75
                    }));

                    const opciones = {
                        chart: {
                            type: 'line',
                            height: 450,
                            stacked: false,
                            zoom: {
                                enabled: true
                            },
                            toolbar: {
                                show: true
                            }
                        },
                        series: [{
                                name: 'Valor Medio',
                                type: 'area',
                                data: datosArea
                            },
                            {
                                name: 'Umbral Máximo',
                                type: 'line',
                                data: umbral
                            }
                        ],
                        stroke: {
                            curve: 'smooth',
                            width: [3, 2]
                        },
                        fill: {
                            type: ['gradient', 'solid'],
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.7,
                                opacityTo: 0.3
                            }
                        },
                        colors: ['#3b82f6', '#ef4444'],
                        markers: {
                            size: 4
                        },
                        xaxis: {
                            type: 'datetime',
                            title: {
                                text: 'Fecha y Hora'
                            }
                        },
                        yaxis: {
                            title: {
                                text: 'Valor'
                            }
                        },
                        tooltip: {
                            shared: true,
                            intersect: false,
                            x: {
                                format: 'dd MMM yyyy - HH:mm'
                            }
                        },
                        grid: {
                            borderColor: '#e7e7e7',
                            row: {
                                colors: ['#f3f3f3', 'transparent'],
                                opacity: 0.5
                            }
                        }
                    };

                    if (grafico) {
                        grafico.updateOptions(opciones);
                        grafico.updateSeries(opciones.series);
                    } else {
                        grafico = new ApexCharts(document.querySelector("#grafico"), opciones);
                        grafico.render();
                    }

                } catch (err) {
                    console.error("Error cargando datos:", err);
                    divSinDatos.innerText = 'Error al cargar los datos.';
                    divSinDatos.style.display = 'block';
                }
            }

            selector.addEventListener('change', cargarGrafico);
            cargarGrafico();
        });
    </script>

</body>

</html>
