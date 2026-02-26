<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmbalsesController;

Route::get('/login', function () {
    return view('auth.login_umbrales');
});

Route::post('/login', [AuthController::class, 'login']);

Route::get('/inicio', function () {
    return view('auth.inicio_umbrales');
});
Route::get('/crearUsuario', function () {
    return view('auth.crearUsuario');
});

Route::post('/crearUsuario', [UserController::class, 'guardarUsuario']);

Route::get('/cerrarSesion', [AuthController::class, 'cerrarSesion']);

Route::get('/datos/e/{id}', [EmbalsesController::class, 'verCCAA']);

Route::get('/buscar-embalse', [EmbalsesController::class, 'buscar'])->name('embalses.buscar');
