<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión Umbrales</title>
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
    </style>
</head>

<body>
    @inject('servicioEpisodios', 'App\Services\EpisodioService')

    <header class="header">
        <div class="usuario">
            Bienvenido, <strong>{{ session('usuario') }}</strong>
            <span>
                (@if (session('is_superuser'))
                    Cuenta: Superusuario
                @elseif(session('is_staff'))
                    Cuenta: Staff
                @else
                    Cuenta: Usuario Normal
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
            <div class="sidebar-titulo">Gestión de Episodios</div>
            <ul style="list-style: none; padding: 0; margin: 0;">

                <li>
                    <a href="/inicio" class="label-desplegable"><span>Inicio</span></a>
                </li>
                <li class="nav-item">

                    <a class="nav-link {{ request()->routeIs('mapa.global') ? 'active' : '' }}"
                       href="{{ route('mapa.global') }}">
                        Mapa Cuenca del Tajo
                    </a>

                </li>
                <form action="{{ route('buscar.global') }}" method="GET"
                    style="display: flex; flex-grow: 1; max-width: 500px; margin: 0 30px;">
                    <input type="text" name="query" placeholder="Buscador" required
                        style="width: 100%; padding: 8px 15px; border-radius: 20px; border: none; outline: none; font-size: 0.9rem; box-shadow: inset 0 1px 3px rgba(0,0,0,0.2);">
                </form>

                <hr style="border: 0; border-top: 1px solid #eee; margin: 10px 15px;">

                {{-- CUENCA DEL TAJO (GLOBAL) --}}
                <li>
                    <input type="checkbox" id="menu_tajo" class="check-menu">
                    <label for="menu_tajo" class="label-desplegable">
                        <span>Cuenca del Tajo (Global)</span>
                        <span class="flecha">▶</span>
                    </label>
                    <ul class="submenu">
                        <li><a href="#">Estado Actual</a></li>
                        <li><a href="{{ route('tajo.activos') }}">Episodios Activos</a></li>
                        <li><a href="{{ route('tajo.historico') }}">Histórico Episodios</a></li>
                    </ul>
                </li>

                {{-- COMUNIDADES AUTÓNOMAS --}}
                @php
                    $comunidades = $servicioEpisodios->obtenerComunidadesConEpisodios();
                @endphp

                @foreach ($comunidades as $ccaa)
                    <li>
                        <input type="checkbox" id="menu_ccaa_{{ $ccaa->c_id }}" class="check-menu">
                        <label for="menu_ccaa_{{ $ccaa->c_id }}" class="label-desplegable">
                            <span>{{ $ccaa->nombre }}</span>
                            <span class="flecha">▶</span>
                        </label>
                        <ul class="submenu">
                            <li><a href="#">Estado Actual</a></li>
                            <li><a href="{{ route('ccaa.activos', $ccaa->c_id) }}">Episodios Activos</a></li>
                            <li><a href="{{ route('ccaa.historico', $ccaa->c_id) }}">Histórico Episodios</a></li>
                        </ul>
                    </li>
                @endforeach

                {{-- GESTIÓN DE USUARIOS (PROBABLEMENTE EN DESUSO) --}}
                @if (session('is_superuser'))
                    <div class="sidebar-titulo" style="margin-top:30px;">Gestión Usuarios</div>
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


    @vite(['resources/js/app.js'])
    <script type="module">
        document.addEventListener('DOMContentLoaded', () => {
            if (window.Echo) {
                console.log("Auto carga activada correctamente");

                window.Echo.channel('panel-alertas')
                    .listen('NuevoCambioRecibido', (e) => {
                        console.log("Nuevo cambio recibido");
                        window.location.reload();
                    });
            }
        });
    </script>
</body>

</html>
