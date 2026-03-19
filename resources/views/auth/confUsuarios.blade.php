@extends('auth.plantilla')

@section('contenido')

<style>

/* CONTENEDOR GENERAL */

.panel-admin{
    max-width:1200px;
    margin:40px auto;
}

/* CABECERA */

.panel-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
}

.panel-left{
    display:flex;
    align-items:center;
    gap:15px;
}

.panel-header h2{
    margin:0;
    font-size:1.6rem;
    font-weight:600;
    color:#1f2937;
}

.badge-total{
    background:#1f2937;
    color:white;
    padding:6px 14px;
    border-radius:20px;
    font-size:0.8rem;
    font-weight:600;
}

/* BOTONES */

.btn{
    border:none;
    border-radius:6px;
    padding:8px 14px;
    font-size:0.85rem;
    font-weight:600;
    cursor:pointer;
    text-decoration:none;
    transition:all .2s;
}

.btn-volver{
    background:#6b7280;
    color:white;
}

.btn-volver:hover{
    background:#4b5563;
}

.btn-anadir{
    background:#2563eb;
    color:white;
}

.btn-anadir:hover{
    background:#1d4ed8;
}

.btn-guardar{
    background:#16a34a;
    color:white;
    padding:10px 20px;
}

.btn-guardar:hover{
    background:#15803d;
}

.btn-editar{
    border:1px solid #2563eb;
    color:#2563eb;
    background:white;
}

.btn-editar:hover{
    background:#2563eb;
    color:white;
}

.btn-eliminar{
    border:1px solid #dc2626;
    color:#dc2626;
    background:white;
}

.btn-eliminar:hover{
    background:#dc2626;
    color:white;
}

/* FORMULARIO */

#contenedor-formulario{
    display:none;
    margin-top:15px;
    margin-bottom:25px;
}

.card-formulario{
    background:white;
    border-radius:10px;
    border:1px solid #e5e7eb;
    box-shadow:0 4px 16px rgba(0,0,0,0.04);
    padding:25px;
    animation:fadeIn .25s ease;
}

.form-header h3{
    margin:0;
    font-size:1.2rem;
    color:#111827;
}

.form-header p{
    margin:4px 0 18px 0;
    font-size:0.85rem;
    color:#6b7280;
}

@keyframes fadeIn{
    from{opacity:0;transform:translateY(-5px);}
    to{opacity:1;transform:translateY(0);}
}

/* GRID FORMULARIO */

.form-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:18px;
}

/* CAMPOS */

.form-group{
    display:flex;
    flex-direction:column;
}

.form-label{
    font-size:0.75rem;
    font-weight:600;
    text-transform:uppercase;
    letter-spacing:.5px;
    color:#6b7280;
    margin-bottom:4px;
}

.input-conf{
    padding:9px 10px;
    border:1px solid #d1d5db;
    border-radius:6px;
    font-size:0.85rem;
    transition:border .2s, box-shadow .2s;
}

.input-conf:focus{
    outline:none;
    border-color:#2563eb;
    box-shadow:0 0 0 2px rgba(37,99,235,0.15);
}

/* FOOTER FORMULARIO */

.form-footer{
    margin-top:10px;
    display:flex;
    justify-content:flex-end;
}

/* TABLA */

.card-tabla{
    background:white;
    border-radius:10px;
    border:1px solid #e5e7eb;
    box-shadow:0 4px 14px rgba(0,0,0,0.04);
    overflow:hidden;
}

.tabla-hidro{
    width:100%;
    border-collapse:collapse;
}

.tabla-hidro thead{
    background:#f9fafb;
}

.tabla-hidro th{
    text-align:left;
    padding:14px;
    font-size:0.75rem;
    font-weight:700;
    color:#6b7280;
    text-transform:uppercase;
    letter-spacing:.6px;
}

.tabla-hidro td{
    padding:14px;
    font-size:0.85rem;
    border-top:1px solid #f1f5f9;
}

.tabla-hidro tbody tr:hover{
    background:#f9fafb;
}

/* ROLES */

.pill-rol{
    padding:5px 12px;
    border-radius:20px;
    font-size:0.7rem;
    font-weight:700;
    text-transform:uppercase;
}

.pill-superuser{
    background:#fee2e2;
    color:#b91c1c;
}

.pill-staff{
    background:#fef3c7;
    color:#92400e;
}

.pill-normal{
    background:#e2e8f0;
    color:#334155;
}

/* ALERTA */

.alert-success{
    background:#ecfdf5;
    border:1px solid #bbf7d0;
    color:#065f46;
    padding:10px;
    border-radius:6px;
    margin-bottom:15px;
    font-size:0.85rem;
}

.error-msg{
    color:#dc2626;
    font-size:0.75rem;
    font-weight:600;
    margin-top:4px;
}

</style>


<div class="panel-admin">


{{-- CABECERA --}}

<div class="panel-header">

<div class="panel-left">
<a href="javascript:history.back()" class="btn btn-volver">Volver</a>
<h2>Gestión de Usuarios</h2>
</div>

<span class="badge-total">
{{ count($usuarios) }} Usuarios
</span>

</div>


<button onclick="toggleFormulario()" class="btn btn-anadir" id="btn-toggle">
Añadir nuevo usuario
</button>


{{-- FORMULARIO --}}

<div id="contenedor-formulario">

<div class="card-formulario">

<div class="form-header">
<h3>Crear nuevo usuario</h3>
<p>Introduce la información necesaria para registrar un usuario.</p>
</div>

<form action="/crearUsuario" method="POST">
@csrf

<div class="form-grid">

<div class="form-group">
<label class="form-label">Username</label>
<input type="text" name="username" class="input-conf" value="{{ old('username') }}" required>
</div>

<div class="form-group">
<label class="form-label">Email</label>
<input type="email" name="email" class="input-conf" value="{{ old('email') }}" required>
</div>

<div class="form-group">
<label class="form-label">Nombre</label>
<input type="text" name="first_name" class="input-conf" value="{{ old('first_name') }}" required>
</div>

<div class="form-group">
<label class="form-label">Apellidos</label>
<input type="text" name="last_name" class="input-conf" value="{{ old('last_name') }}" required>
</div>

<div class="form-group">
<label class="form-label">Contraseña</label>
<input type="password" name="password" class="input-conf" required>
@error('password')
<span class="error-msg">La contraseña no cumple los requisitos</span>
@enderror
</div>

<div class="form-group">
<label class="form-label">Confirmar contraseña</label>
<input type="password" name="password_confirmation" class="input-conf" required>
</div>

<div class="form-group">
<label class="form-label">Rol</label>
<select name="rol" class="input-conf">
<option value="usuario">Usuario</option>
<option value="staff">Staff</option>
<option value="superuser">Superusuario</option>
</select>
</div>

</div>

<div class="form-footer">
<button type="submit" class="btn btn-guardar">
Guardar usuario
</button>
</div>

</form>

</div>

</div>


{{-- TABLA --}}

<div class="card-tabla">

@if (session('exito') || session('success'))
<div class="alert-success">
{{ session('exito') ?? session('success') }}
</div>
@endif


<table class="tabla-hidro">

<thead>
<tr>
<th>Username</th>
<th>Nombre</th>
<th>Email</th>
<th>Rol</th>
<th style="text-align:center;">Acciones</th>
</tr>
</thead>

<tbody>

@foreach($usuarios as $u)

<tr>

<td><strong>{{ $u->username }}</strong></td>

<td>{{ $u->first_name }} {{ $u->last_name }}</td>

<td>{{ $u->email }}</td>

<td>

@if($u->is_superuser)
<span class="pill-rol pill-superuser">Superusuario</span>

@elseif($u->is_staff)
<span class="pill-rol pill-staff">Staff</span>

@else
<span class="pill-rol pill-normal">Usuario</span>
@endif

</td>

<td style="text-align:center">

<div style="display:flex;gap:8px;justify-content:center">

<a href="{{ route('editarUsuario', $u->id) }}" class="btn btn-editar">
Editar
</a>

@if(auth()->id() !== $u->id)

<form action="{{ route('eliminarUsuario', $u->id) }}" method="POST"
onsubmit="return confirm('¿Eliminar usuario {{ $u->username }}?');">

@csrf
@method('DELETE')

<button class="btn btn-eliminar">
Eliminar
</button>

</form>

@else

<small style="color:#9ca3af;">Sesión actual</small>

@endif

</div>

</td>

</tr>

@endforeach

</tbody>

</table>

</div>

</div>


<script>

function toggleFormulario(){

const div=document.getElementById('contenedor-formulario');
const btn=document.getElementById('btn-toggle');

if(div.style.display==='none'||div.style.display===''){

div.style.display='block';
btn.innerText='Cancelar';
btn.style.background='#6b7280';

}else{

div.style.display='none';
btn.innerText='Añadir nuevo usuario';
btn.style.background='#2563eb';

}

}

@if ($errors->any())
document.addEventListener('DOMContentLoaded',()=>{toggleFormulario();});
@endif

</script>

@endsection
