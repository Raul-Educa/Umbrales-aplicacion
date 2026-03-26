@extends('auth.plantilla')

@section('contenido')
    <style>
        .tabla-episodios {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 0.75rem;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            table-layout: fixed;
        }

        .tabla-episodios th,
        .tabla-episodios td {
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
        }

        .tabla-episodios th {
            text-align: left;
            padding: 10px 6px;
            border-bottom: 2px solid #dee2e6;
            color: #000;
            font-weight: 700;
            vertical-align: bottom;
            line-height: 1.2;
        }

        .tabla-episodios td {
            padding: 10px 6px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
            color: #333;
            line-height: 1.4;
        }

        .tabla-episodios tbody tr {
            transition: background-color 0.2s ease;
        }

        .tabla-episodios tbody tr:hover {
            background-color: #f4f6f9;
        }

        .btn-ver {
            background: #0d6efd;
            color: white;
            padding: 6px 8px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.75rem;
            font-weight: 500;
            transition: background 0.2s;
            display: inline-block;
            text-align: center;
            width: 100%;
            box-sizing: border-box;
        }

        .btn-ver:hover {
            background: #0b5ed7;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .col-numero { width: 7%; }
        .col-nombre { width: 13%; }
        .col-ccaa { width: 11%; }
        .col-afectadas { width: 23%; }
        .col-alarmadas { width: 14%; }
        .col-fecha-ini { width: 7%; }
        .col-fecha-fin { width: 7%; }
        .col-boletines { width: 8%; }
        .col-acciones { width: 10%; }
    </style>

    <h2>{{ $titulo }}</h2>

    @if (count($episodios) > 0)
        <table class="tabla-episodios">
            <thead>
                <tr>
                    <th class="col-numero">Nº Episodio</th>
                    <th class="col-nombre">Nombre</th>
                    <th class="col-ccaa">Comunidad Autónoma</th>
                    <th class="col-afectadas">Estaciones afectadas en el episodio</th>
                    <th class="col-alarmadas">Estaciones alarmadas actualmente</th>
                    <th class="col-fecha-ini">Iniciado</th>
                    <th class="col-fecha-fin">Finalizado</th>
                    <th class="col-boletines">Boletines generados</th>
                    <th class="col-acciones">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($episodios as $ep)
                    <tr>
                        <td>{{ $ep->re_id }}</td>

                        <td>
                            @if (!empty($ep->re_nombre) && $ep->re_nombre !== 'None')
                                {{ $ep->re_nombre }}
                            @else
                                ---
                            @endif
                        </td>

                        <td class="uppercase">{{ $ep->nombre_ccaa ?? '---' }}</td>

                        <td>{{ $ep->re_estaciones_historicas ?? '---' }}</td>

                       <td>
                            @if(!empty($ep->re_hora_fin))
                                {{-- Si tiene hora de fin, es HISTÓRICO. No muestra alarmas actuales. --}}
                                <span style="color: #999;">---</span>
                            @else
                                {{-- Si NO tiene hora de fin, es ACTIVO. Calculamos cuáles hay ahora mismo. --}}
                                @php
                                    $alarmadasReales = [];
                                    if(!empty($ep->re_estaciones_historicas)) {
                                        $todas = array_filter(array_map('trim', explode(',', $ep->re_estaciones_historicas)));
                                        foreach($todas as $cod) {
                                            if(isset($nivelesEstaciones[$cod]) && $nivelesEstaciones[$cod] > 0) {
                                                $alarmadasReales[] = $cod;
                                            }
                                        }
                                    }
                                @endphp

                                @if(count($alarmadasReales) > 0)
                                    {{ implode(', ', $alarmadasReales) }}
                                @else
                                    <span style="color: #999;">---</span>
                                @endif
                            @endif
                        </td>

                        <td>
                            {!! $ep->re_hora_inicio ? \Carbon\Carbon::parse($ep->re_hora_inicio)->format('m/d/Y<br>H:i') : '---' !!}
                        </td>

                        <td>
                            {!! $ep->re_hora_fin ? \Carbon\Carbon::parse($ep->re_hora_fin)->format('m/d/Y<br>H:i') : '' !!}
                        </td>

                        <td>{{ $ep->re_boletines_generados ?? '0' }}</td>

                        <td>
                            <a href="{{ route('episodios.detalle', $ep->re_id) }}" class="btn-ver">Ver detalle</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="padding: 20px; color: #999; background: #f8f9fa; border-radius: 8px; margin-top: 20px;">
            No hay episodios {{ strtolower($tipo) }} registrados en esta sección.
        </p>
    @endif
@endsection
