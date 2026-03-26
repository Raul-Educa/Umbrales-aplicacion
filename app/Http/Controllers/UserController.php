<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserController extends Controller
{
   // MUestra todos los usuarios por orden de rol
    public function gestionarUsuarios()
    {
        $usuarios = User::orderByDesc('is_superuser')
                        ->orderByDesc('is_staff')
                        ->get();

        return view('auth.confUsuarios', compact('usuarios'));
    }

    // Logia crear usuario
  public function guardarUsuario(Request $request)
{
    $request->validate([
        'username'   => 'required|unique:auth_user,username',
        'email'      => 'required|email|unique:auth_user,email',
        'password'   => 'required|confirmed|min:6', // <--- 'confirmed' hace la magia
        'first_name' => 'required',
        'last_name'  => 'required',
        'rol'        => 'required'
    ]);

    $is_superuser = $request->rol == 'superuser';
    $is_staff = ($request->rol == 'staff' || $request->rol == 'superuser');

    \Illuminate\Support\Facades\DB::table('auth_user')->insert([
        'username'     => $request->username,
        'email'        => $request->email,
        'password'     => \Illuminate\Support\Facades\Hash::make($request->password),
        'is_superuser' => $is_superuser,
        'is_staff'     => $is_staff,
        'is_active'    => true,
        'date_joined'  => now(),
        'first_name'   => $request->first_name,
        'last_name'    => $request->last_name
    ]);

    return redirect()->route('confUsuarios')->with('exito', 'Usuario creado correctamente.');
}
   // Logica eliminar usuario
    public function eliminarUsuario($id)
    {
        $user = User::findOrFail($id);

        if (\Illuminate\Support\Facades\Auth::id() == $user->id) {
            return back()->with('error', '¡No puedes borrarte a ti mismo!');
        }

        $user->delete();

        return back()->with('success', 'Usuario eliminado con éxito.');
    }

   //Logica editar usuario
public function mostrarFormularioEditar($id)
{
    $usuario = User::findOrFail($id);
    return view('auth.editarUsuario', compact('usuario'));
}

public function actualizarUsuario(Request $request, $id)
{
    $usuario = User::findOrFail($id);
    $esMiPropioUsuario = (\Illuminate\Support\Facades\Auth::id() == $id);

    $reglas = [
        'first_name' => 'required|string|max:150',
        'last_name'  => 'required|string|max:150',
        'email'      => 'required|email|unique:auth_user,email,'.$id,
    ];

    if (!$esMiPropioUsuario) {
        $reglas['rol'] = 'required|in:usuario,staff,superuser';
    }

    $request->validate($reglas);

    $datos = [
        'first_name' => $request->first_name,
        'last_name'  => $request->last_name,
        'email'      => $request->email,
    ];

    if (!$esMiPropioUsuario) {
        $datos['is_superuser'] = ($request->rol == 'superuser');
        $datos['is_staff']     = ($request->rol == 'staff' || $request->rol == 'superuser');
    }

    $usuario->update($datos);

    return redirect()->route('confUsuarios')->with('success', 'Usuario actualizado correctamente.');
}
}
