<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;

use App\Http\Controllers\permisoscontroller;

use Illuminate\Support\Facades\Route;



use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\productocontroller;
use App\Http\Controllers\caracteristicacontroller;
use App\Http\Controllers\opcionescontroller;
use App\Http\Controllers\ProyectosController;

use App\Events\TestEvent;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

//resources\views\auth\login.blade.php
Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// proyectos
Route::get('/proyectos',[ProyectosController::class, 'index'])->middleware(['auth','verified'])->name('proyectos.index');
Route::get('/proyectos/{proyecto}', [ProyectosController::class, 'show'])->name('proyecto.show');

//Administracion de usuarios
Route::get('/usuarios',[UserController::class, 'index'])->middleware(['auth','verified'])->name('usuarios.index');
Route::get('/usuarios/modal',[UserController::class, 'actions'])->middleware(['auth','verified'])->name('usuarios.actions');
// permisos
Route::get('/usuarios/permisos',[permisoscontroller::class, 'index'])->middleware(['auth','verified'])->name('permisos.index');

//Catalogos
    //Categorias
    // Route::get('/catalogos/categorias',[categoriacontroller::class, 'index'])->name('catalogos.categorias.index');
    Route::get('catalogos/categorias', [CategoriaController::class, 'index'])->name('catalogos.categorias.index');
    Route::get('catalogos/producto',   [productocontroller::class, 'index'])->name('catalogos.producto.index');
    Route::get('catalogos/caracteristicas', [caracteristicacontroller::class, 'index'])->name('catalogos.caracteristica.index');
    Route::get('catalogos/opciones', [opcionescontroller::class, 'index'])->name('catalogos.opciones.index');

// Prueba data tables

// Prueba de funcionalidad de los web sokets


Route::post('/emit-event', function (Illuminate\Http\Request $request) {
    $message = $request->input('message', 'Mensaje predeterminado');
 
    broadcast(new TestEvent($message));
    return response()->json(['status' => 'Evento emitido']);
});

require __DIR__.'/auth.php';
