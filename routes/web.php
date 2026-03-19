<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GraficoController;
use App\Http\Controllers\EpisodioController;
use App\Http\Controllers\EmergenciaController;
// Estos son antiguos, los dejo por si en un futuro hacen falta

// use App\Http\Controllers\EmbalsesController;
// use App\Http\Controllers\RoeasController;
// use App\Http\Controllers\MarcoControlController;
// use App\Http\Controllers\AforoRiosController;


// AUTENTICACIÓN
Route::get('/inicio', [EpisodioController::class, 'inicio'])->name('inicio');

// 1. Mostrar el formulario (GET)
Route::get('/login', function () {
    return view('auth.login_umbrales');
})->name('login');

// 2. Procesar el login (POST)
Route::post('/login', [AuthController::class, 'login']);

Route::get('/cerrarSesion', [AuthController::class, 'cerrarSesion']);

// ==========================================
// VISTAS Y GESTIÓN DE USUARIOS
// ==========================================

// 1. Mostrar la página (Llama a la función gestionarUsuarios de tu controlador)
Route::get('/confUsuarios', [UserController::class, 'gestionarUsuarios'])->name('confUsuarios');

// 2. Procesar la creación del usuario (POST)
Route::post('/crearUsuario', [UserController::class, 'guardarUsuario']);

// 3. Eliminar usuario (DELETE)
Route::delete('/eliminarUsuario/{id}', [UserController::class, 'eliminarUsuario'])->name('eliminarUsuario');
// Rutas para Editar Usuario
Route::get('/editarUsuario/{id}', [UserController::class, 'mostrarFormularioEditar'])->name('editarUsuario');
Route::put('/actualizarUsuario/{id}', [UserController::class, 'actualizarUsuario'])->name('actualizarUsuario');

// Cuenca del Tajo (Global)
Route::get('/tajo/activos', [EpisodioController::class, 'activosGlobal'])->name('tajo.activos');
Route::get('/tajo/historico', [EpisodioController::class, 'historicoGlobal'])->name('tajo.historico');

//Comunidades Autónomas por ID
Route::get('/ccaa/{id}/activos', [EpisodioController::class, 'activosPorCCAA'])->name('ccaa.activos');
Route::get('/ccaa/{id}/historico', [EpisodioController::class, 'historicoPorCCAA'])->name('ccaa.historico');


// GRÁFICOS
Route::get('/grafico/{codigo}', [GraficoController::class, 'verGrafico'])->name('ver.grafico');
Route::get('/api/historial/{codigo}', [GraficoController::class, 'obtenerHistorial'])->name('api.grafico');

Route::get('/episodio/{id}/detalle', [EpisodioController::class, 'detalle'])->name('episodios.detalle');

Route::get('/buscar', [App\Http\Controllers\EpisodioController::class, 'buscarGlobal'])->name('buscar.global');

// Rutas de administración para episodios
Route::post('/episodio/{id}/cerrar', [EpisodioController::class, 'cerrarEpisodio'])->name('episodio.cerrar');
Route::post('/episodio/{id}/renombrar', [EpisodioController::class, 'renombrarEpisodio'])->name('episodio.renombrar');


//Ruta para el mapa global del Tajo
Route::get('/mapaGeneralTajo', [App\Http\Controllers\EpisodioController::class, 'mapaGlobal'])->name('mapa.global');




// Rutas para el gestor de emergencias
Route::get('/emergencias/nueva', [EmergenciaController::class, 'crear'])->name('emergencias.crear');
Route::post('/emergencias/guardar', [EmergenciaController::class, 'guardar'])->name('emergencias.guardar');

Route::get('/emergencias/vista-plan', [EmergenciaController::class, 'vistaPlanEmergencia'])->name('emergencias.vistaPlan');



/*
Solo la he usado o se usara para recargar desde la URL
Route::get('/limpiar-cache-oculta', function () {
    Cache::flush();
    return response()->json(['status' => 'Memoria de la web borrada']);
});
*/
// Estos son antiguos, los dejo por si en un futuro hacen falta

// Route::get('/datos/e/{id}', [EmbalsesController::class, 'verCCAA']);
// Route::get('/buscar-embalse', [EmbalsesController::class, 'buscar'])->name('embalses.buscar');
// Route::get('/datos/r/general', [RoeasController::class, 'mostrarDetalle'])->name('roeas.detalle');
// Route::get('/datos/mc/{zona}', [MarcoControlController::class, 'detalle'])->name('mc.detalle');
// Route::get('/datos/ar/{ccaa_id}', [AforoRiosController::class, 'detalle'])->name('ar.detalle');
