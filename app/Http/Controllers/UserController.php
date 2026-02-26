<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function guardarUsuario(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:auth_user,username',
            'email' => 'required|email|unique:auth_user,email',
            'password' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'rol' => 'required'
        ]);

        $is_superuser = $request->rol == 'superuser' ? true : false;
        $is_staff = $request->rol == 'staff' ? true : false;

        DB::table('auth_user')->insert([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_superuser' => $is_superuser,
            'is_staff' => $is_staff,
            'is_active' => true,
            'date_joined' => now(),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name
        ]);
        return redirect('/crearUsuario')->with('exito', 'Usuario creado correctamente.');
    }
}
