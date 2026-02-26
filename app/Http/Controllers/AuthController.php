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

        $passwordCifrada = Hash::make($request->password);
        $usuario = DB::table('auth_user')
            ->where('username', $request->username)
            ->first();

        if ($usuario && Hash::check($request->password, $usuario->password)) {
            session([
                'usuario' => $usuario->username,
                'email' => $usuario->email,
                'is_superuser' => $usuario->is_superuser,
                'is_staff' => $usuario->is_staff
                ]);

            return redirect('/inicio');
        }
        return back()->withErrors([
            'username' => 'Usuario o contraseña incorrectos.',
        ])->withInput();
    }
    public function cerrarSesion()
    {
        session()->flush();
        return redirect('/login');
    }
}
