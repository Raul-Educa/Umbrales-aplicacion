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
            background: #f8f9fa;
        }

        .tabla-hidro td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 0.85rem;
            vertical-align: middle;
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

        .pill-a3 {
            background-color: #ef4444;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.75rem;
            margin-left: 5px;
            font-weight: bold;
        }

        .pill-a2 {
            background-color: #f97316;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.75rem;
            margin-left: 5px;
            font-weight: bold;
        }

        .pill-a1 {
            background-color: #eab308;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.75rem;
            margin-left: 5px;
            font-weight: bold;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #eee;
        }

        .section-header h3 {
            margin: 0;
            color: #333;
            display: flex;
            align-items: center;
        }

        .val-box {
            font-size: 1.05rem;
            font-weight: bold;
        }

        .estado-anterior {
            font-size: 0.75rem;
            color: #888;
            margin-top: 4px;
            display: block;
        }
    </style>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0;">{{ $titulo }}</h2>
        <a href="javascript:history.back()"
            style="background: #6c757d; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.85rem;">←
            Volver</a>
    </div>

    {{-- ================= EMBALSES ================= --}}
    @php
        $embalsesA3 = $embalses->where('nivel_alerta', 3)->count();
        $embalsesA2 = $embalses->where('nivel_alerta', 2)->count();
        $embalsesA1 = $embalses->where('nivel_alerta', 1)->count();
    @endphp
    <div class="section-header">
        <h3>
            Embalses
            @if ($embalsesA3 > 0)
                <span class="pill-a3">{{ $embalsesA3 }}</span>
            @endif
            @if ($embalsesA2 > 0)
                <span class="pill-a2">{{ $embalsesA2 }}</span>
            @endif
            @if ($embalsesA1 > 0)
                <span class="pill-a1">{{ $embalsesA1 }}</span>
            @endif
        </h3>
        <span class="pill-global">{{ count($embalses) }} encontrados</span>
    </div>

    @if (count($embalses) > 0)
        <table class="tabla-hidro">
            <thead>
                <tr>
                    <th style="width: 30%;">Nombre / Río</th>
                    <th style="width: 20%; text-align: center;">Valor y Hora</th>
                    <th style="width: 25%;">Tags</th>
                    <th style="width: 25%;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($embalses as $e)
                    <tr class="fila-alerta-{{ $e->nivel_alerta ?? 0 }}">
                        <td>
                            <strong>{{ $e->er_nombre }}</strong><br>
                            <small style="color: #666;">Río: {{ $e->er_rio ?? '---' }}</small>
                        </td>
                        <td style="text-align: center;">
                            <div class="val-box">{{ $e->rde_valor ? number_format($e->rde_valor, 2, ',', '.') : '---' }}
                            </div>
                            <small style="color: #666;">{{ $e->rde_hora ?? '---' }}</small>
                        </td>
                        <td style="line-height: 1.4;">
                            <small><b>IP21:</b> {{ $e->er_tag_ip21 ?? '---' }}</small><br>
                            <small><b>VOL:</b> {{ $e->er_tag_volumen ?? '---' }}</small><br>
                            <small><b>DIG:</b> {{ $e->er_tag_digital_ip21 ?? '---' }}</small>
                        </td>
                        <td>
                            <div>
                                <span class="status-dot dot-{{ $e->nivel_alerta ?? 0 }}"></span>
                                @if (($e->nivel_alerta ?? 0) == 3)
                                    <b style="color:#ef4444">ALERTA 3</b>
                                @elseif(($e->nivel_alerta ?? 0) == 2)
                                    <b style="color:#f97316">ALERTA 2</b>
                                @elseif(($e->nivel_alerta ?? 0) == 1)
                                    <b style="color:#eab308">ALERTA 1</b>
                                @else
                                    <span style="color:gray">NORMAL</span>
                                @endif
                            </div>
                            <div class="estado-anterior">
                                Último: <span class="status-dot dot-{{ $e->er_ultimo_nivel_alerta ?? 0 }}"
                                    style="height: 8px; width: 8px;"></span> Nivel {{ $e->er_ultimo_nivel_alerta ?? 0 }}
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="padding: 15px; color: #999; background: #f8f9fa; border-radius: 6px;">No se encontraron embalses.</p>
    @endif

    {{-- ================= ROEAS ================= --}}
    @php
        $roeasA3 = $roeas->where('nivel_alerta', 3)->count();
        $roeasA2 = $roeas->where('nivel_alerta', 2)->count();
        $roeasA1 = $roeas->where('nivel_alerta', 1)->count();
    @endphp
    <div class="section-header">
        <h3>
            Roeas
            @if ($roeasA3 > 0)
                <span class="pill-a3">{{ $roeasA3 }}</span>
            @endif
            @if ($roeasA2 > 0)
                <span class="pill-a2">{{ $roeasA2 }}</span>
            @endif
            @if ($roeasA1 > 0)
                <span class="pill-a1">{{ $roeasA1 }}</span>
            @endif
        </h3>
        <span class="pill-global">{{ count($roeas) }} encontradas</span>
    </div>

    @if (count($roeas) > 0)
        <table class="tabla-hidro">
            <thead>
                <tr>
                    <th style="width: 30%;">Activa / Nombre / Zona</th>
                    <th style="width: 20%; text-align: center;">Valor y Hora</th>
                    <th style="width: 25%;">Tags</th>
                    <th style="width: 25%;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($roeas as $r)
                    <tr class="fila-alerta-{{ $r->nivel_alerta ?? 0 }}"
                        style="{{ $r->ur_activo ? '' : 'opacity: 0.6;' }}">
                        <td>
                            {{ $r->ur_activo ? 'ACTIVO' : 'INACTIVO' }} - <strong>{{ $r->ur_nombre }}</strong><br>
                            <small style="color: #666;">Río: {{ $r->ur_rio ?? '---' }} | Zona:
                                {{ $r->ur_zona_explotacion ?? '---' }}</small>
                        </td>
                        <td style="text-align: center;">
                            <div class="val-box">{{ $r->rde_valor ? number_format($r->rde_valor, 2, ',', '.') : '---' }}
                            </div>
                            <small style="color: #666;">{{ $r->rde_hora ?? '---' }}</small>
                        </td>
                        <td style="line-height: 1.4;">
                            <small><b>IP21:</b> {{ $r->ur_tag_ip21 ?? '---' }}</small><br>
                            <small><b>CAUD:</b> {{ $r->ur_tag_ip21_caudal ?? '---' }}</small><br>
                            <small><b>DIG:</b> {{ $r->ur_tag_digital_ip21 ?? '---' }}</small>
                        </td>
                        <td>
                            <div>
                                <span class="status-dot dot-{{ $r->nivel_alerta ?? 0 }}"></span>
                                @if (($r->nivel_alerta ?? 0) == 3)
                                    <b style="color:#ef4444">ALERTA 3</b>
                                @elseif(($r->nivel_alerta ?? 0) == 2)
                                    <b style="color:#f97316">ALERTA 2</b>
                                @elseif(($r->nivel_alerta ?? 0) == 1)
                                    <b style="color:#eab308">ALERTA 1</b>
                                @else
                                    <span style="color:gray">NORMAL</span>
                                @endif
                            </div>
                            <div class="estado-anterior">
                                Último: <span class="status-dot dot-{{ $r->ur_ultimo_nivel_alerta ?? 0 }}"
                                    style="height: 8px; width: 8px;"></span> Nivel {{ $r->ur_ultimo_nivel_alerta ?? 0 }}
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="padding: 15px; color: #999; background: #f8f9fa; border-radius: 6px;">No se encontraron roeas.</p>
    @endif

    {{-- ================= MARCOS DE CONTROL ================= --}}
    @php
        $mcA3 = $marcos_control->where('nivel_alerta', 3)->count();
        $mcA2 = $marcos_control->where('nivel_alerta', 2)->count();
        $mcA1 = $marcos_control->where('nivel_alerta', 1)->count();
    @endphp
    <div class="section-header">
        <h3>
            Marcos de Control
            @if ($mcA3 > 0)
                <span class="pill-a3">{{ $mcA3 }}</span>
            @endif
            @if ($mcA2 > 0)
                <span class="pill-a2">{{ $mcA2 }}</span>
            @endif
            @if ($mcA1 > 0)
                <span class="pill-a1">{{ $mcA1 }}</span>
            @endif
        </h3>
        <span class="pill-global">{{ count($marcos_control) }} encontrados</span>
    </div>

    @if (count($marcos_control) > 0)
        <table class="tabla-hidro">
            <thead>
                <tr>
                    <th style="width: 30%;">Activa / Nombre / Zona</th>
                    <th style="width: 20%; text-align: center;">Valor y Hora</th>
                    <th style="width: 25%;">Tags</th>
                    <th style="width: 25%;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($marcos_control as $mc)
                    <tr class="fila-alerta-{{ $mc->nivel_alerta ?? 0 }}"
                        style="{{ $mc->ur_activo ? '' : 'opacity: 0.6;' }}">
                        <td>
                            {{ $mc->ur_activo ? 'ACTIVO' : 'INACTIVO' }} - <strong>{{ $mc->ur_codigo }}</strong><br>
                            <strong>{{ $mc->ur_nombre }}</strong><br>
                            <small style="color: #666;">Río: {{ $mc->ur_rio ?? '---' }} | Zona:
                                {{ $mc->ur_zona_explotacion ?? '---' }}</small>
                        </td>
                        <td style="text-align: center;">
                            <div class="val-box">{{ $mc->rde_valor ? number_format($mc->rde_valor, 2, ',', '.') : '---' }}
                            </div>
                            <small style="color: #666;">{{ $mc->rde_hora ?? '---' }}</small>
                        </td>
                        <td style="line-height: 1.4;">
                            <small><b>IP21:</b> {{ $mc->ur_tag_ip21 ?? '---' }}</small><br>
                            <small><b>CAUD:</b> {{ $mc->ur_tag_ip21_caudal ?? '---' }}</small><br>
                            <small><b>DIG:</b> {{ $mc->ur_tag_digital_ip21 ?? '---' }}</small>
                        </td>
                        <td>
                            <div>
                                <span class="status-dot dot-{{ $mc->nivel_alerta ?? 0 }}"></span>
                                @if (($mc->nivel_alerta ?? 0) == 3)
                                    <b style="color:#ef4444">ALERTA 3</b>
                                @elseif(($mc->nivel_alerta ?? 0) == 2)
                                    <b style="color:#f97316">ALERTA 2</b>
                                @elseif(($mc->nivel_alerta ?? 0) == 1)
                                    <b style="color:#eab308">ALERTA 1</b>
                                @else
                                    <span style="color:gray">NORMAL</span>
                                @endif
                            </div>
                            <div class="estado-anterior">
                                Último: <span class="status-dot dot-{{ $mc->ur_ultimo_nivel_alerta ?? 0 }}"
                                    style="height: 8px; width: 8px;"></span> Nivel {{ $mc->ur_ultimo_nivel_alerta ?? 0 }}
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="padding: 15px; color: #999; background: #f8f9fa; border-radius: 6px;">No se encontraron Marcos de Control.
        </p>
    @endif

    {{-- ================= AFOROS EN RÍOS ================= --}}
    @php
        $arA3 = $aforos->where('nivel_alerta', 3)->count();
        $arA2 = $aforos->where('nivel_alerta', 2)->count();
        $arA1 = $aforos->where('nivel_alerta', 1)->count();
    @endphp
    <div class="section-header">
        <h3>
            Aforos en Ríos
            @if ($arA3 > 0)
                <span class="pill-a3">{{ $arA3 }}</span>
            @endif
            @if ($arA2 > 0)
                <span class="pill-a2">{{ $arA2 }}</span>
            @endif
            @if ($arA1 > 0)
                <span class="pill-a1">{{ $arA1 }}</span>
            @endif
        </h3>
        <span class="pill-global">{{ count($aforos) }} encontrados</span>
    </div>

    @if (count($aforos) > 0)
        <table class="tabla-hidro">
            <thead>
                <tr>
                    <th style="width: 30%;">Código / Nombre</th>
                    <th style="width: 20%; text-align: center;">Valor y Hora</th>
                    <th style="width: 25%;">Tags (IP21 / Caudal / Digital)</th>
                    <th style="width: 25%;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($aforos as $ar)
                    <tr class="fila-alerta-{{ $ar->nivel_alerta ?? 0 }}"
                        style="{{ $ar->ur_activo ? '' : 'opacity: 0.6;' }}">
                        <td>
                            {{ $ar->ur_activo ? 'ACTIVO' : 'INACTIVO' }} - <strong>{{ $ar->ur_codigo }}</strong><br>
                            <strong>{{ $ar->ur_nombre }}</strong><br>
                            <small style="color: #666;">Río: {{ $ar->ur_rio ?? '---' }} | Zona:
                                {{ $ar->ur_zona_explotacion ?? '---' }}</small>
                        </td>
                        <td style="text-align: center;">
                            <div class="val-box">{{ $ar->rde_valor ? number_format($ar->rde_valor, 2, ',', '.') : '---' }}
                            </div>
                            <small style="color: #666;">{{ $ar->rde_hora ?? '---' }}</small>
                        </td>
                        <td style="line-height: 1.4;">
                            <small><b>IP21:</b> {{ $ar->ur_tag_ip21 ?? '---' }}</small><br>
                            <small><b>CAUD:</b> {{ $ar->ur_tag_ip21_caudal ?? '---' }}</small><br>
                            <small><b>DIG:</b> {{ $ar->ur_tag_digital_ip21 ?? '---' }}</small>
                        </td>
                        <td>
                            <div>
                                <span class="status-dot dot-{{ $ar->nivel_alerta ?? 0 }}"></span>
                                @if (($ar->nivel_alerta ?? 0) == 3)
                                    <b style="color:#ef4444">ALERTA 3</b>
                                @elseif(($ar->nivel_alerta ?? 0) == 2)
                                    <b style="color:#f97316">ALERTA 2</b>
                                @elseif(($ar->nivel_alerta ?? 0) == 1)
                                    <b style="color:#eab308">ALERTA 1</b>
                                @else
                                    <span style="color:gray">NORMAL</span>
                                @endif
                            </div>
                            <div class="estado-anterior">
                                Último: <span class="status-dot dot-{{ $ar->ur_ultimo_nivel_alerta ?? 0 }}"
                                    style="height: 8px; width: 8px;"></span> Nivel {{ $ar->ur_ultimo_nivel_alerta ?? 0 }}
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="padding: 15px; color: #999; background: #f8f9fa; border-radius: 6px;">No se encontraron Aforos en Ríos.
        </p>
    @endif

@endsection
