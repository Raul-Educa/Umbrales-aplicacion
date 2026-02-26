<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión</title>
    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100%;
        }

        .header {
            height: 50px;
            background-color: #777777;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 25px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-sizing: border-box;
        }

        .header .usuario {
            font-weight: 500;
            font-size: 0.9rem;
        }

        .header .email {
            font-size: 0.8rem;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .wrapper {
            display: flex;
            margin-top: 50px;
            height: calc(100vh - 50px);
        }

        .sidebar {
            width: 260px;
            background-color: #5f666d;
            color: #ecf0f1;
            padding-top: 20px;
            flex-shrink: 0;
            overflow-y: auto;
        }

        .sidebar-titulo {
            padding: 0 20px 15px 20px;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #a4b7c2;
            letter-spacing: 1px;
        }

        .main-content {
            flex-grow: 1;
            padding: 30px;
            background-color: #f8f9fa;
            overflow-y: auto;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            min-height: 200px;
        }

        .btn-logout {
            text-decoration: none;
            color: #ffffff;
            border: 1px solid #ffffff;
            padding: 5px 12px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.3s ease;
            background: transparent;
        }

        .btn-logout:hover {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
        }

        .check-menu {
            display: none;
        }

        .label-desplegable {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 20px;
            color: #ecf0f1;
            cursor: pointer;
            transition: background 0.3s;
            font-size: 0.9rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            text-decoration: none;
        }

        .label-desplegable:hover {
            background-color: #4e545a;
        }

        .submenu {
            max-height: 0;
            overflow: hidden;
            background-color: #495057;
            transition: max-height 0.4s ease-out;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .submenu li a {
            display: block;
            padding: 10px 40px;
            font-size: 0.85rem;
            color: #cbd5e0;
            text-decoration: none;
        }

        .submenu li a:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .check-menu:checked+.label-desplegable+.submenu {
            max-height: 500px;
        }

        .flecha {
            font-size: 0.7rem;
            transition: transform 0.3s;
        }

        .check-menu:checked+.label-desplegable .flecha {
            transform: rotate(90deg);
        }

        .contenedor-badges {
            display: flex;
            gap: 4px;
            align-items: center;
        }

        .badge-alerta {
            display: inline-block;
            min-width: 20px;
            height: 20px;
            padding: 0 4px;
            line-height: 17px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            border: 2px solid;
        }

        .alerta-3 {
            border-color: #ff0000;
            color: #ff0000;
            background: rgba(255, 0, 0, 0.1);
        }

        .alerta-2 {
            border-color: #ff8c00;
            color: #ff8c00;
            background: rgba(255, 140, 0, 0.1);
        }

        .alerta-1 {
            border-color: #ffd700;
            color: #ffd700;
            background: rgba(255, 215, 0, 0.1);
        }

        .contenedor-badges {
            display: flex;
            gap: 4px;
        }

        .badge-alerta {
            display: inline-block;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            line-height: 15px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 800;
            text-align: center;
            border: 2px solid;
        }

        .alerta-3 {
            border-color: #d32f2f;
            color: #000000;
            background: #ffebee;
        }

        .alerta-2 {
            border-color: #f57c00;
            color: #000000;
            background: #fff3e0;
        }

        .alerta-1 {
            border-color: #fbc02d;
            color: #000000;
            background: #fff9c4;
        }

        .alerta-0 {
            border-color: #9e9e9e;
            color: #000000;
            background: #f5f5f5;
        }

        .search-container {
            padding: 10px;
            display: flex;
            justify-content: center;
        }

        .search-box {
            position: relative;
            display: flex;
            align-items: center;
            width: 85%;
            max-width: 200px;
        }

        .search-input {
            width: 100%;
            padding: 10px 35px 10px 15px;
            border-radius: 8px;
            border: 1px solid #ced4da;
            background-color: #f8f9fa;
            font-size: 13px;
            outline: none;
            transition: border-color 0.2s;
        }

        .search-input:focus {
            border-color: #007bff;
            background-color: #fff;
        }

        .search-button {
            position: absolute;
            right: 5px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.1s;
        }

        .search-button span {
            font-size: 22px;
            color: #000000;
            line-height: 1;
            font-weight: bold;
        }

        .search-button:hover {
            transform: scale(1.2);
        }

        .menu-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .badge-container {
            display: flex;
            gap: 3px;
        }

        .badge-alerta {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            font-size: 10px;
            font-weight: 700;
            color: #fff;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .alerta-3 {
            background-color: #d32f2f;
        }

        .alerta-2 {
            background-color: #f57c00;
        }

        .alerta-1 {
            background-color: #fbc02d;
            color: #333;
        }


        .pill-global {
            background-color: #455a64;
            color: #ffffff;
            padding: 1px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }
    </style>
</head>

<body>

    <header class="header">
        <div class="usuario">
            Bienvenido, <strong>{{ session('usuario') }}</strong>
            <span>
                (@if (session('is_superuser'))
                    Superusuario
                @elseif(session('is_staff'))
                    Staff
                @else
                    Usuario Normal
                @endif)
            </span>
        </div>
        <div class="email">
            <strong>{{ session('email') }}</strong>
            <a href="/cerrarSesion" class="btn-logout">Cerrar Sesión</a>
        </div>
    </header>

    <div class="wrapper">
        <nav class="sidebar">
            <div class="sidebar-titulo">Navegación de Datos</div>
            <ul style="list-style: none; padding: 0; margin: 0;">
                <li>
                    <a href="/inicio" class="label-desplegable">
                        <span>Inicio</span>
                    </a>
                </li>


                <div class="search-container">
                    <form action="{{ route('embalses.buscar') }}" method="GET" class="search-box">
                        <input type="text" name="q" class="search-input" placeholder="Buscador"
                            value="{{ request('q') }}">
                        <button type="submit" class="search-button">
                            <span>🡆</span>
                        </button>
                    </form>
                </div>
                <hr style="border: 0; border-top: 1px solid #eee; margin: 0 15px 10px 15px;">
                <hr style="border: 0; border-top: 1px solid #eee; margin: 0 10px;">
                <li>
                    <input type="checkbox" id="ar" class="check-menu">
                    <label for="ar" class="label-desplegable">
                        <span>Aforo en Ríos</span>
                        <span class="flecha">▶</span>
                    </label>
                    <ul class="submenu">
                        <li><a href="/datos/ar/madrid">Madrid</a></li>
                        <li><a href="/datos/ar/toledo">Toledo</a></li>
                    </ul>
                </li>

                <li>
                    <input type="checkbox" id="mc" class="check-menu">
                    <label for="mc" class="label-desplegable">
                        <span>Marco de Control</span>
                        <span class="flecha">▶</span>
                    </label>
                    <ul class="submenu">
                        <li><a href="/datos/mc/zona1">Zona Centro</a></li>
                    </ul>
                </li>

                <li>
                    <input type="checkbox" id="r" class="check-menu">
                    <label for="r" class="label-desplegable">
                        <span>Rodeas</span>
                        <span class="flecha">▶</span>
                    </label>
                    <ul class="submenu">
                        <li><a href="/datos/r/general">Vista General</a></li>
                    </ul>
                </li>

                <li>
                    <input type="checkbox" id="e" class="check-menu">
                    <label for="e" class="label-desplegable">
                        <span>
                            <span>Embalses</span>

                            @if (isset($totalGlobal) && $totalGlobal > 0)
                                <span class="pill-global">{{ $totalGlobal }}</span>
                            @endif
                        </span> <span class="flecha">▶</span>
                    </label>
                    <ul class="submenu">

                        @php
                            $comunidades = [
                                8 => 'Castilla La Mancha',
                                11 => 'Extremadura',
                                13 => 'Madrid',
                                7 => 'Castilla y León',
                            ];
                        @endphp

                       @foreach ($comunidades as $id => $nombre)
            <li>
                <a href="/datos/e/{{ $id }}" style="display:flex; justify-content: space-between; align-items: center; padding: 8px 15px;">
                    <span style="font-size: 13px;">{{ $nombre }}</span>

                    <div class="contenedor-badges">
                        @if (isset($resumenAlertas[$id]))
                            @foreach ($resumenAlertas[$id]->sortByDesc('nivel') as $alerta)
                                <span class="badge-alerta alerta-{{ $alerta->nivel }}">
                                    {{ $alerta->total }}
                                </span>
                            @endforeach
                        @else
                            <span class="badge-alerta alerta-0">0</span>
                        @endif
                    </div>
                </a>
            </li>
        @endforeach
                    </ul>
                </li>

                @if (session('is_superuser'))
                    <div class="sidebar-titulo" style="margin-top:20px;">Seguridad</div>
                    <li>
                        <a href="/crearUsuario" class="label-desplegable">
                            <span>Crear Usuario</span>
                        </a>
                    </li>
                @endif
            </ul>
        </nav>

        <main class="main-content">
            <div class="card">
                @yield('contenido')
            </div>
        </main>
    </div>

</body>

</html>
