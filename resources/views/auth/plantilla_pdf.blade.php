<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Situación - {{ strtoupper($ccaa) }}</title>

<style>
    /* =============================================
       CONFIGURACIÓN DE PÁGINA (Márgenes reales)
    ============================================= */
    @page {
        /* Arriba: 2.5cm | Derecha: 2cm | Abajo: 3cm | Izquierda: 2cm */
        margin: 2.5cm 2cm 3cm 2cm;
    }

    body {
        font-family: "Georgia", "Times New Roman", serif;
        font-size: 9.5pt;
        color: #1a1a1a;
        line-height: 1.65;
        margin: 0;
        padding: 0;
    }

    /* =============================================
       CABECERA INSTITUCIONAL
    ============================================= */
    .cabecera-institucional {
        background-color: #1a3a5c;
        /* Tiramos de la cabecera hacia los bordes absolutos del folio */
        margin-top: -2.5cm;
        margin-left: -2cm;
        margin-right: -2cm;
        margin-bottom: 25px; /* Da aire al título que viene debajo */
    }

    .cabecera-banda-superior {
        background-color: #0f2540;
        height: 6px;
    }

    .cabecera-contenido {
        padding: 18px 2cm 16px 2cm;
        display: table;
        width: 100%;
        box-sizing: border-box;
    }

    .cabecera-escudo-celda {
        display: table-cell;
        vertical-align: middle;
        width: 70px;
        padding-right: 18px;
    }

    .escudo-svg {
        width: 52px;
        height: 52px;
    }

    .cabecera-texto-celda {
        display: table-cell;
        vertical-align: middle;
    }

    .cabecera-ministerio {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 7pt;
        color: #8eaec9;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        margin: 0 0 3px 0;
    }

    .cabecera-organismo {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 12.5pt;
        font-weight: bold;
        color: #ffffff;
        letter-spacing: 0.3px;
        margin: 0 0 1px 0;
    }

    .cabecera-subtitulo {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 7.5pt;
        color: #8eaec9;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        margin: 0;
    }

    .cabecera-linea-derecha {
        display: table-cell;
        vertical-align: middle;
        text-align: right;
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
    }

    .cabecera-ref-doc {
        font-size: 7pt;
        color: #8eaec9;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .cabecera-banda-inferior {
        background-color: #c8a84b;
        height: 3px;
    }

    /* =============================================
       ÁREA DE CONTENIDO PRINCIPAL
    ============================================= */
    .contenido-principal {
        /* Ya no necesitamos padding porque @page ya tiene márgenes */
        padding: 0;
    }

    .titulo-documento {
        margin-bottom: 28px;
        border-bottom: 1px solid #d0d8e0;
        padding-bottom: 16px;
    }

    .titulo-tipo-documento {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 7pt;
        font-weight: bold;
        color: #1a3a5c;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin: 0 0 6px 0;
    }

    .titulo-principal {
        font-family: "Georgia", serif;
        font-size: 17pt;
        font-weight: bold;
        color: #0f2540;
        margin: 0 0 4px 0;
        letter-spacing: -0.3px;
        line-height: 1.2;
    }

    .titulo-subtitulo {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 9pt;
        color: #4a6070;
        margin: 0;
    }

    .ficha-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 32px;
        background-color: #f5f7fa;
        border: 1px solid #dde3ea;
    }

    .ficha-table-header {
        background-color: #e8edf3;
        padding: 7px 14px;
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 7pt;
        font-weight: bold;
        color: #1a3a5c;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        border-bottom: 1px solid #c8d0da;
    }

    .ficha-table td {
        padding: 9px 14px;
        border-bottom: 1px solid #e2e7ec;
        vertical-align: middle;
        /* Evita que textos larguísimos rompan la tabla */
        word-wrap: break-word;
        word-break: break-word;
        overflow-wrap: break-word;
    }

    .ficha-table tr:last-child td {
        border-bottom: none;
    }

    .ficha-label {
        width: 180px;
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 7.5pt;
        font-weight: bold;
        color: #4a6070;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .ficha-value {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 9.5pt;
        color: #0f2540;
        font-weight: 500;
    }

    .seccion {
        margin-bottom: 28px;
    }

    .seccion-cabecera {
        display: table;
        width: 100%;
        margin-bottom: 12px;
    }

    .seccion-titulo {
        display: table-cell;
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 8.5pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        color: #1a3a5c;
        border-bottom: 1px solid #1a3a5c;
        padding-bottom: 6px;
    }

    .contenido-texto {
        font-family: "Georgia", serif;
        font-size: 9.5pt;
        text-align: justify;
        color: #2a2a2a;
        line-height: 1.7;
        padding-left: 28px;
        /* Evita desbordes de texto */
        word-wrap: break-word;
        word-break: break-word;
        overflow-wrap: break-word;
    }

    .contenido-texto p {
        margin: 0 0 11px 0;
    }

    .contenido-texto p:last-child {
        margin-bottom: 0;
    }

    /* =============================================
       PIE DE PÁGINA FIJO
    ============================================= */
    .pie-pagina {
        position: fixed;
        /* Anclamos el pie al fondo real del folio, ignorando el margen inferior */
        bottom: -3cm;
        left: -2cm;
        right: -2cm;
        height: 2.2cm;
        background-color: #f5f7fa;
        border-top: 1px solid #d0d8e0;
    }

    .pie-banda {
        background-color: #1a3a5c;
        height: 3px;
    }

    .pie-contenido {
        display: table;
        width: 100%;
        padding: 0 2cm;
        box-sizing: border-box;
        height: calc(2.2cm - 3px);
    }

    .pie-izquierda {
        display: table-cell;
        vertical-align: middle;
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 7pt;
        color: #6a7a8a;
    }

    .pie-organismo {
        font-weight: bold;
        color: #1a3a5c;
    }

    .pie-derecha {
        display: table-cell;
        vertical-align: middle;
        text-align: right;
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 7pt;
        color: #6a7a8a;
    }

    .no-break {
        page-break-inside: avoid;
    }

    .linea-divisora {
        border: none;
        border-top: 1px solid #e0e6ec;
        margin: 24px 0;
    }
</style>
</head>

<body>

<!-- =============================================
     PIE DE PÁGINA FIJO
============================================= -->
<div class="pie-pagina">
    <div class="pie-banda"></div>
    <div class="pie-contenido">
        <div class="pie-izquierda">
            <div class="pie-organismo">Confederación Hidrográfica del Tajo</div>
            <div>Ministerio para la Transición Ecológica y el Reto Demográfico</div>
        </div>
        <div class="pie-derecha">
            <div>Documento generado automáticamente</div>
            <div>{{ \Carbon\Carbon::now()->format('d/m/Y \a \l\a\s H:i') }} h &nbsp;|&nbsp; USO INTERNO</div>
        </div>
    </div>
</div>


<div class="cabecera-institucional">
    <div class="cabecera-banda-superior"></div>
    <div class="cabecera-contenido">

        <div class="cabecera-escudo-celda">
            <svg class="escudo-svg" viewBox="0 0 52 52" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="escudoGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" style="stop-color:#c8d8e8;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#8eaec9;stop-opacity:1" />
                    </linearGradient>
                </defs>
                <path d="M6 4 H46 V32 Q26 50 6 32 Z" fill="none" stroke="url(#escudoGrad)" stroke-width="1.5"/>
                <rect x="14" y="2" width="3" height="5" rx="1" fill="#c8a84b"/>
                <rect x="24.5" y="1" width="3" height="6" rx="1" fill="#c8a84b"/>
                <rect x="35" y="2" width="3" height="5" rx="1" fill="#c8a84b"/>
                <path d="M13 6 H39 V9 H13 Z" fill="#c8a84b"/>
                <path d="M12 20 Q16 17 20 20 Q24 23 28 20 Q32 17 36 20 Q38 21 40 20"
                      stroke="#8eaec9" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                <path d="M12 25 Q16 22 20 25 Q24 28 28 25 Q32 22 36 25 Q38 26 40 25"
                      stroke="#6a9ab8" stroke-width="1.2" fill="none" stroke-linecap="round"/>
                <text x="26" y="16" text-anchor="middle"
                      font-family="Georgia, serif" font-size="8" font-weight="bold"
                      fill="#c8d8e8" letter-spacing="0.5">CHT</text>
            </svg>
        </div>

        <div class="cabecera-texto-celda">
            <div class="cabecera-ministerio">Ministerio para la Transición Ecológica y el Reto Demográfico</div>
            <div class="cabecera-organismo">Confederación Hidrográfica del Tajo</div>
            <div class="cabecera-subtitulo">Sistema de Control y Vigilancia de Cuenca</div>
        </div>

        <div class="cabecera-linea-derecha">
            <div class="cabecera-ref-doc">N.º Documento</div>
        </div>

    </div>
    <div class="cabecera-banda-inferior"></div>
</div>


<div class="contenido-principal">

    <div class="titulo-documento no-break">
        <div class="titulo-tipo-documento">Informe Técnico &mdash; Reporte de Situación</div>
        <div class="titulo-principal">REPORTE DE SITUACIÓN AUTOMATIZADO</div>
        <div class="titulo-subtitulo">Cuenca Hidrográfica del Tajo &mdash; Área de Seguimiento y Emergencias</div>
    </div>

    <!-- Ficha informativa -->
    <table class="ficha-table no-break">
        <tr>
            <td colspan="3" class="ficha-table-header">Datos de identificación del evento</td>
        </tr>
        <tr>
            <td class="ficha-label">Comunidad autónoma afectada:</td>
            <td class="ficha-value">{{ strtoupper($ccaa) }}</td>
        </tr>

        <tr>
            <td class="ficha-label">Provincias afectadas:</td>
            <td class="ficha-value">{{ $provincias }}</td>
        </tr>
        <tr>
            <td class="ficha-label">Fecha del evento:</td>
            <td class="ficha-value">{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="ficha-label">Hora de registro:</td>
            <td class="ficha-value">{{ $hora }} h</td>
        </tr>
        <tr>
            <td class="ficha-label">Fecha de emisión:</td>
            <td class="ficha-value">{{ \Carbon\Carbon::now()->format('d/m/Y \a \l\a\s H:i') }} h</td>
        </tr>
        <tr>
            <td class="ficha-label">Organismo emisor:</td>
            <td class="ficha-value">Confederación Hidrográfica del Tajo — Sistema Automático</td>
        </tr>
    </table>

    <!-- Sección: Descripción del evento -->
    <div class="seccion">
        <div class="seccion-cabecera">
              <div class="seccion-titulo">Descripción del evento</div>
        </div>
        <div class="contenido-texto">
            {!! nl2br(e($texto)) !!}
        </div>
    </div>

    <hr class="linea-divisora">



</div>

</body>
</html>
