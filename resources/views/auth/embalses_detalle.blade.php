@extends('auth.plantilla')

@section('contenido')
<style>
    .tabla-hidro { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .tabla-hidro th { text-align: left; padding: 12px; border-bottom: 2px solid #dee2e6; color: #555; }
    .tabla-hidro td { padding: 12px; border-bottom: 1px solid #eee; font-size: 0.9rem; }

    .fila-alerta-3 { background-color: rgba(255, 0, 0, 0.15) !important; font-weight: bold; }
    .fila-alerta-2 { background-color: rgba(255, 140, 0, 0.15) !important; }
    .fila-alerta-1 { background-color: rgba(255, 215, 0, 0.15) !important; }

    .status-dot { height: 10px; width: 10px; border-radius: 50%; display: inline-block; margin-right: 5px; }
    .dot-3 { background-color: red; box-shadow: 0 0 5px red; }
    .dot-2 { background-color: orange; }
    .dot-1 { background-color: gold; }
    .dot-0 { background-color: #bbb; }
</style>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>{{ $titulo }}</h2>
    <small style="color: #666;">Última actualización: {{ now()->format('H:i') }}</small>
</div>

<table class="tabla-hidro">
    <thead>
        <tr>
            <th>Embalse</th>
            <th>Río</th>
            <th>Umbral</th>
            <th>Hora</th>
            <th>Ultimo Estado</th>
            <th>Estado Actual</th>
        </tr>
    </thead>
    <tbody>
        @forelse($embalses as $e)
            <tr class="fila-alerta-{{ $e->nivel_alerta }}">
                <td>{{ $e->er_nombre }}</td>
                <td>{{ $e->er_rio }}</td>

                <td><strong>{{ $e->rde_valor ?? '---' }}</strong></td>

                <td>{{ $e->rde_hora ?? 'No hay hora registrada' }}</td>

<td>
                    <span class="status-dot dot-{{ $e->er_ultimo_nivel_alerta }}"></span>
                    @if($e->nivel_alerta == 3) <b style="color:red">ALERTA 3</b>
                    @elseif($e->nivel_alerta == 2) <b style="color:orange">ALERTA 2</b>
                    @elseif($e->nivel_alerta == 1) <b style="color: gold">ALERTA 1</b>
                    @else <span style="color:gray">NORMAL</span>
                    @endif
                </td>                <td>
                    <span class="status-dot dot-{{ $e->nivel_alerta }}"></span>
                    @if($e->nivel_alerta == 3) <b style="color:red">ALERTA 3</b>
                    @elseif($e->nivel_alerta == 2) <b style="color:orange">ALERTA 2</b>
                    @elseif($e->nivel_alerta == 1) <b style="color: gold">ALERTA 1</b>
                    @else <span style="color:gray">NORMAL</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 40px; color: #999;">
                    <i class="fas fa-info-circle"></i> No hay embalses registrados en la configuración para esta zona.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

@if(count($embalses) == 0)
    <p style="text-align: center; padding: 50px; color: #999;">No hay datos disponibles para esta comunidad.</p>
@endif

@endsection
