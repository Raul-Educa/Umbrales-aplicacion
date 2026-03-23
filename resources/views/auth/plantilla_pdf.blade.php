<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Situación - {{ strtoupper($ccaa) }}</title>

<style>
    /* CONFIGURACIÓN DE PÁGINA */
    @page {
        margin: 2cm 2cm 2.5cm 2cm; /* Márgenes optimizados para aprovechar el espacio */
    }

    body {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 10pt;
        color: #222;
        line-height: 1.5;
        margin: 0;
        padding: 0;
    }

    /* CABECERA (Alineación izquierda, estilo ejecutivo) */
    .cabecera {
        border-bottom: 1px solid #ccc;
        padding-bottom: 15px;
        margin-bottom: 30px;
    }

    .cabecera-organismo {
        font-size: 8pt;
        color: #666;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .cabecera-titulo {
        font-size: 18pt;
        font-weight: bold;
        color: #000;
        margin: 0;
        letter-spacing: -0.5px;
    }

    /* BLOQUE INFO (Diseño en tabla para 100% compatibilidad con DomPDF/mPDF) */
    .info-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 35px;
    }

    .info-table td {
        padding: 8px 0;
        border-bottom: 1px solid #eee;
        vertical-align: top;
    }

    .info-label {
        width: 160px;
        font-size: 8.5pt;
        font-weight: bold;
        color: #555;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-value {
        font-size: 10pt;
        color: #000;
        font-weight: 500;
    }

    /* SECCIÓN DE CONTENIDO */
    .seccion {
        margin-bottom: 30px;
    }

    .seccion-titulo {
        font-size: 10pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #000;
        border-bottom: 1px solid #000;
        padding-bottom: 5px;
        margin-bottom: 15px;
    }

    .contenido {
        text-align: justify;
        color: #333;
    }

    /* Sin sangrías de novela. Párrafos en bloque para informes profesionales */
    .contenido p {
        margin: 0 0 12px 0;
    }

    /* BLOQUE DE TRAZABILIDAD (Minimalista en bloque gris) */
    .meta-block {
        margin-top: 40px;
        background-color: #f9f9f9;
        padding: 12px 15px;
        border-left: 3px solid #ccc;
        font-size: 8pt;
        color: #555;
        page-break-inside: avoid; /* Evita que se parta a la mitad en 2 páginas */
    }

    .meta-item {
        margin-bottom: 4px;
    }
    .meta-item:last-child {
        margin-bottom: 0;
    }

    /* PIE DE PÁGINA */
    .pie-pagina {
        position: fixed;
        bottom: -1cm;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 7.5pt;
        color: #999;
        border-top: 1px solid #eee;
        padding-top: 8px;
    }
</style>
</head>

<body>

<div class="pie-pagina">
    Sistema de Control de Cuenca &nbsp;|&nbsp; Documento generado automáticamente el {{ \Carbon\Carbon::now()->format('d/m/Y \a \l\a\s H:i') }}
</div>

<div class="cabecera">
    <div class="cabecera-organismo">Sistema de Control de Cuenca</div>
    <div class="cabecera-titulo">REPORTE DE SITUACIÓN AUTOMATIZADO</div>
</div>

<table class="info-table">
    <tr>
        <td class="info-label">Zona afectada</td>
        <td class="info-value">{{ strtoupper($ccaa) }}</td>
    </tr>
    <tr>
        <td class="info-label">Fecha del evento</td>
        <td class="info-value">{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</td>
    </tr>
    <tr>
        <td class="info-label">Hora de registro</td>
        <td class="info-value">{{ $hora }} h</td>
    </tr>
</table>

<div class="seccion">
    <div class="seccion-titulo">Descripción del evento</div>
    <div class="contenido">
        {!! nl2br(e($texto)) !!}
    </div>
</div>



</body>
</html>
