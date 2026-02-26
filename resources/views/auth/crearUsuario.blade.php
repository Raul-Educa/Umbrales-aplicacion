@extends('auth.plantilla')

@section('contenido')
    <h2>Crear Nuevo Usuario</h2>
    <p style="color: #666;">Completa los datos para dar de alta a un nuevo usuario.</p>
    <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

    @if(session('exito'))
        <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
            {{ session('exito') }}
        </div>
    @endif

    <form action="/crearUsuario" method="POST" style="max-width: 500px;">
        @csrf
        <div style="margin-bottom: 15px;">
            <label style="display:block; margin-bottom:5px; font-weight:bold;">Username:</label>
            <input type="text" name="username" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;" required>
        </div>

        <div style="display:flex; gap:10px; margin-bottom: 15px;">
            <div style="flex:1;">
                <label style="display:block; margin-bottom:5px; font-weight:bold;">Nombre:</label>
                <input type="text" name="first_name" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;" required>
            </div>
            <div style="flex:1;">
                <label style="display:block; margin-bottom:5px; font-weight:bold;">Apellidos:</label>
                <input type="text" name="last_name" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;" required>
            </div>
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display:block; margin-bottom:5px; font-weight:bold;">Email:</label>
            <input type="email" name="email" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;" required>
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display:block; margin-bottom:5px; font-weight:bold;">Contraseña:</label>
            <input type="password" name="password" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;" required>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display:block; margin-bottom:5px; font-weight:bold;">Rol de Usuario:</label>
            <select name="rol" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;" required>
                <option value="usuario">Usuario Normal</option>
                <option value="staff">Staff</option>
                <option value="superuser">Superusuario</option>
            </select>
        </div>

        <button type="submit" style="background: #3aa3e0; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
            Crear Nuevo Usuario
        </button>
    </form>
@endsection
