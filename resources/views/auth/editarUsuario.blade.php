@extends('auth.plantilla')

@section('contenido')

<style>

.panel-edicion{
    max-width:720px;
    margin:40px auto;
    background:#ffffff;
    padding:35px;
    border-radius:10px;
    border:1px solid #e6e6e6;
    box-shadow:0 6px 22px rgba(0,0,0,0.05);
}

.panel-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.panel-header h2{
    margin:0;
    font-size:1.35rem;
    font-weight:600;
    color:#1f2937;
}

.panel-header span{
    color:#2563eb;
    font-weight:600;
}

.boton-volver{
    text-decoration:none;
    font-size:0.85rem;
    color:#6b7280;
    border:1px solid #d1d5db;
    padding:6px 12px;
    border-radius:5px;
    transition:all .2s;
}

.boton-volver:hover{
    background:#f3f4f6;
}

.divider{
    border:none;
    border-top:1px solid #eee;
    margin-bottom:25px;
}

.form-group{
    margin-bottom:22px;
}

.form-label{
    display:block;
    margin-bottom:6px;
    font-size:0.8rem;
    font-weight:600;
    color:#6b7280;
    text-transform:uppercase;
    letter-spacing:.4px;
}

.form-input{
    width:100%;
    padding:10px 12px;
    border:1px solid #d1d5db;
    border-radius:6px;
    font-size:0.9rem;
    transition:border .2s, box-shadow .2s;
}

.form-input:focus{
    outline:none;
    border-color:#2563eb;
    box-shadow:0 0 0 3px rgba(37,99,235,0.1);
}

.form-readonly{
    background:#f9fafb;
    color:#6b7280;
}

.grid-2{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
}

.rol-bloqueado{
    padding:10px 12px;
    border:1px solid #d1d5db;
    border-radius:6px;
    background:#f9fafb;
    color:#374151;
    font-size:0.9rem;
}

.rol-bloqueado small{
    display:block;
    font-size:0.75rem;
    color:#6b7280;
    margin-top:3px;
}

.boton-guardar{
    width:100%;
    background:#2563eb;
    color:white;
    padding:12px;
    border:none;
    border-radius:6px;
    font-size:0.95rem;
    font-weight:600;
    cursor:pointer;
    transition:background .2s, transform .1s;
}

.boton-guardar:hover{
    background:#1d4ed8;
}

.boton-guardar:active{
    transform:scale(0.98);
}

</style>


<div class="panel-edicion">

    <div class="panel-header">
        <h2>Estás editando a: <span>{{ $usuario->username }}</span></h2>
        <a href="{{ route('confUsuarios') }}" class="boton-volver">Volver</a>
    </div>

    <hr class="divider">

    <form action="{{ route('actualizarUsuario', $usuario->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label class="form-label">Username</label>
            <input type="text" value="{{ $usuario->username }}"
                   class="form-input form-readonly"
                   readonly>
        </div>

        <div class="grid-2">

            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email"
                       name="email"
                       value="{{ old('email', $usuario->email) }}"
                       class="form-input"
                       required>
            </div>

            <div class="form-group">
                <label class="form-label">Rol del usuario</label>

                @if(auth()->id() == $usuario->id)

                    <div class="rol-bloqueado">
                        {{ $usuario->is_superuser ? 'Superusuario' : ($usuario->is_staff ? 'Staff' : 'Normal') }}
                        <small>No puedes modificar tu propio rol</small>
                    </div>

                @else

                    <select name="rol" class="form-input" required>
                        <option value="usuario" {{ (!$usuario->is_superuser && !$usuario->is_staff) ? 'selected' : '' }}>Usuario</option>
                        <option value="staff" {{ (!$usuario->is_superuser && $usuario->is_staff) ? 'selected' : '' }}>Staff</option>
                        <option value="superuser" {{ $usuario->is_superuser ? 'selected' : '' }}>Superusuario</option>
                    </select>

                @endif

            </div>

        </div>

        <div class="grid-2">

            <div class="form-group">
                <label class="form-label">Nombre</label>
                <input type="text"
                       name="first_name"
                       value="{{ old('first_name', $usuario->first_name) }}"
                       class="form-input"
                       required>
            </div>

            <div class="form-group">
                <label class="form-label">Apellidos</label>
                <input type="text"
                       name="last_name"
                       value="{{ old('last_name', $usuario->last_name) }}"
                       class="form-input"
                       required>
            </div>

        </div>

        <button type="submit" class="boton-guardar">
            Guardar cambios
        </button>

    </form>

</div>

@endsection
