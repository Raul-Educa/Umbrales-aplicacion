<!DOCTYPE html>
<html lang="es">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestión Umbrales</title>

<style>

/* ===== BASE ===== */

body,html{
margin:0;
padding:0;
font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,sans-serif;
height:100%;
background:#eef1f4;
color:#1f2937;
overflow:hidden; /* IMPORTANTE: elimina scroll exterior */
}

/* ===== HEADER ===== */

.header{
height:60px;
background:#3f4750;
color:white;
display:flex;
justify-content:space-between;
align-items:center;
padding:0 30px;
box-shadow:0 2px 8px rgba(0,0,0,0.15);
position:fixed;
top:0;
width:100%;
z-index:1000;
box-sizing:border-box;
}

.usuario{
font-size:0.9rem;
}

.usuario strong{
font-weight:600;
}

.email{
display:flex;
align-items:center;
gap:15px;
font-size:0.85rem;
opacity:.95;
}

/* ===== LOGOUT ===== */

.btn-logout{
text-decoration:none;
color:white;
border:1px solid rgba(255,255,255,.4);
padding:6px 14px;
border-radius:6px;
font-size:0.8rem;
font-weight:600;
transition:all .2s;
}

.btn-logout:hover{
background:#ef4444;
border-color:#ef4444;
}

/* ===== LAYOUT ===== */

.wrapper{
display:flex;
margin-top:60px;
height:calc(100vh - 60px);
overflow:hidden;
}

/* ===== SIDEBAR ===== */

.sidebar{
width:250px;
background:#5b636c;
color:#e5e7eb;
padding-top:15px;
overflow-y:auto; /* scroll sidebar */
flex-shrink:0;
box-shadow:2px 0 10px rgba(0,0,0,.1);
}

/* TITULOS */

.sidebar-titulo{
padding:12px 22px;
font-size:.7rem;
text-transform:uppercase;
color:#cbd5e1;
letter-spacing:1px;
font-weight:600;
}

/* LINKS */

.label-desplegable{
display:flex;
justify-content:space-between;
align-items:center;
padding:11px 22px;
font-size:0.9rem;
color:#f1f5f9;
text-decoration:none;
cursor:pointer;
transition:all .2s;
border-left:3px solid transparent;
}

.label-desplegable:hover{
background:#4b525a;
border-left:3px solid #3b82f6;
}

.active-link{
background:#4b525a;
border-left:3px solid #3b82f6;
font-weight:600;
}

/* SUBMENU */

.submenu{
max-height:0;
overflow:hidden;
background:#525960;
transition:max-height .35s ease;
list-style:none;
padding:0;
margin:0;
}

.submenu li a{
display:block;
padding:9px 40px;
font-size:.85rem;
color:#d1d5db;
text-decoration:none;
transition:.2s;
}

.submenu li a:hover{
background:#454c54;
color:white;
}

/* CHECK MENU */

.check-menu{
display:none;
}

.check-menu:checked + .label-desplegable + .submenu{
max-height:500px;
}

/* FLECHA */

.flecha{
font-size:.7rem;
opacity:.7;
transition:transform .25s;
}

.check-menu:checked + .label-desplegable .flecha{
transform:rotate(90deg);
}

/* ===== BUSCADOR ===== */

.sidebar form{
padding:10px 20px 15px 20px;
}

.sidebar input{
width:100%;
padding:8px 12px;
border-radius:6px;
border:none;
font-size:.85rem;
outline:none;
background:#6b7280;
color:white;
}

.sidebar input::placeholder{
color:#e5e7eb;
}

/* ===== CONTENIDO ===== */

.main-content{
flex-grow:1;
padding:35px;
overflow-y:auto; /* único scroll principal */
height:100%;
scroll-behavior:smooth;
}

/* CARD */

.card{
background:white;
padding:30px;
border-radius:10px;
box-shadow:0 5px 16px rgba(0,0,0,.06);
min-height:200px;
}

/* SCROLL SIDEBAR */

.sidebar::-webkit-scrollbar{
width:6px;
}

.sidebar::-webkit-scrollbar-thumb{
background:#4b525a;
border-radius:10px;
}

.main-content::-webkit-scrollbar{
width:8px;
}

.main-content::-webkit-scrollbar-thumb{
background:#cbd5e1;
border-radius:10px;
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

<ul style="list-style:none;padding:0;margin:0;">

<li>
<a href="/inicio" class="label-desplegable">Inicio</a>
</li>

<li>
<a href="{{ route('mapa.global') }}"
class="label-desplegable {{ request()->routeIs('mapa.global') ? 'active-link' : '' }}">
Mapa Cuenca del Tajo
</a>
</li>

<form action="{{ route('buscar.global') }}" method="GET">
<input type="text" name="query" placeholder="Buscador" required>
</form>

<hr style="border:0;border-top:1px solid #ccc;margin:10px 15px;">



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
<div class="sidebar-titulo">Situaciones de Emergencia</div>
<li>
<a href="{{ route('emergencias.vistaPlan') }}" class="label-desplegable">
            <span>Vista Plan Emergencia</span>
    </a>
</li>

<li>
    <a href=""></a>
<a href="{{ route('emergencias.crear') }}" class="label-desplegable">
Formulario Emergencias
</a>
</li>

@if (session('is_superuser'))

<div class="sidebar-titulo">Configuración</div>

<li>
<a href="{{ route('confUsuarios') }}" class="label-desplegable">
Gestionar Usuarios
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
