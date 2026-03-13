<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);
        // Cifra la contraseña
        $passwordCifrada = Hash::make($request->password);
        $usuario = DB::table('auth_user')
            ->where('username', $request->username)
            ->first();
        // Compara la contraseña cifrada
        if ($usuario && Hash::check($request->password, $usuario->password)) {
            // Crea la sessión
            session([
                'usuario' => $usuario->username,
                'email' => $usuario->email,
                'is_superuser' => $usuario->is_superuser,
                'is_staff' => $usuario->is_staff
            ]);

            return redirect('/inicio');
        }

        // Si no es correcto muestra un mensaje de error
        return back()->withErrors([
            'username' => 'Usuario o contraseña incorrectos.',
        ])->withInput();
    }

    // CIerra la session y redirige al login
    public function cerrarSesion()
    {
        session()->flush();
        return redirect('/login');
    }
}
