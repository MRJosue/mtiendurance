<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;

use App\Http\Controllers\permisoscontroller;

use Illuminate\Support\Facades\Route;



use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\productocontroller;
use App\Http\Controllers\caracteristicacontroller;
use App\Http\Controllers\opcionescontroller;
use App\Http\Controllers\ciudadescontroller;
use App\Http\Controllers\tipoenviocontroller;
use App\Http\Controllers\estadoscontroller;
use App\Http\Controllers\paisescontroller;

use App\Http\Controllers\ProyectosController;
use App\Http\Controllers\PreproyectosController;
use App\Http\Controllers\DashboardController;

use App\Http\Controllers\TallasController;

use App\Http\Controllers\DisenioController;

use App\Http\Controllers\ProgramacionController;

use App\Events\TestEvent;
use App\Events\MessageSent;
use App\Events\NewChatMessage;
use App\Models\MensajeChat;

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

use App\Http\Controllers\DemoController;

Route::get('/notificacion', [DemoController::class, 'mostrarNotificacion']);


//resources\views\auth\login.blade.php
Route::get('/', function () {
    return view('auth.login');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');


Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');



Route::get('/MessageSent', function () {
    event(new \App\Events\MessageSent("¡Hola desde el servidor!"));
    return "Evento emitido.";
});


Route::get('/emitir-demo', function () {
    broadcast(new class('Hola desde canal-demo') implements \Illuminate\Contracts\Broadcasting\ShouldBroadcastNow {
        public $mensaje;

        public function __construct($mensaje)
        {
            $this->mensaje = $mensaje;
        }

        public function broadcastOn()
        {
            return new \Illuminate\Broadcasting\Channel('canal-demo');
        }

        public function broadcastAs()
        {
            return 'evento.demo';
        }
    });

    return 'Evento emitido.';
});

Route::view('/demo', 'demo');


Route::get('/ChatMessageTest', function () {
    // Crea un mensaje de ejemplo
    $mensaje = MensajeChat::create([
        'chat_id' => 4, // ID del chat asociado
        'usuario_id' => 1, // ID de un usuario existente
        'mensaje' => 'Este es un mensaje de prueba desde Tinker.',
    ]);

    // Emite el evento
    event(new NewChatMessage($mensaje));
    return "Evento Chat emitido.";
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// proyectos
Route::get('/proyectos',[ProyectosController::class, 'index'])->middleware(['auth','verified'])->name('proyectos.index');
Route::get('/proyectos/{proyecto}', [ProyectosController::class, 'show'])->middleware(['auth', 'verified'])->name('proyecto.show');

//preproyectos
Route::get('/preproyectos',[PreproyectosController::class, 'index'])->middleware(['auth','verified'])->name('preproyectos.index');
Route::get('/preproyectos/show/{preproyecto}', [PreproyectosController::class, 'show'])->name('preproyectos.show');
Route::get('/preproyectos/create',[PreproyectosController::class, 'create'])->middleware(['auth','verified'])->name('preproyectos.create');


//Administracion de usuarios
Route::get('/usuarios',[UserController::class, 'index'])->middleware(['auth','verified'])->name('usuarios.index');
Route::get('/usuarios/crear',[UserController::class, 'create'])->middleware(['auth','verified'])->name('usuarios.create');
Route::get('/usuarios/detalles/{user}',[UserController::class, 'show'])->middleware(['auth','verified'])->name('usuarios.show');
Route::get('/usuarios/modal',[UserController::class, 'actions'])->middleware(['auth','verified'])->name('usuarios.actions');

// permisos
Route::get('/usuarios/permisos',[permisoscontroller::class, 'index'])->middleware(['auth','verified'])->name('permisos.index');

//Rutas Panel de diseño
Route::get('/diseño',[DisenioController::class, 'index'])->middleware(['auth','verified'])->name('disenio.index');
// Administrador de diseño
Route::get('/diseño/disenio_detalle/{proyecto}',[DisenioController::class, 'disenio_detalle'])->middleware(['auth','verified'])->name('disenio.disenio_detalle');
// 
Route::get('/diseño/admin_tarea',[DisenioController::class, 'admin_tarea'])->middleware(['auth','verified'])->name('disenio.admin_tarea');


// Rutas de programacion 
Route::get('/programacion',[ProgramacionController::class, 'index'])->middleware(['auth','verified'])->name('programacion.index');


//Catalogos
    //Categorias
    // Route::get('/catalogos/categorias',[categoriacontroller::class, 'index'])->name('catalogos.categorias.index');
    Route::get('catalogos/categorias', [CategoriaController::class, 'index'])->middleware(['auth', 'verified'])->name('catalogos.categorias.index');
    Route::get('catalogos/producto',   [productocontroller::class, 'index'])->middleware(['auth', 'verified'])->name('catalogos.producto.index');
    Route::get('catalogos/caracteristicas', [caracteristicacontroller::class, 'index'])->middleware(['auth', 'verified'])->name('catalogos.caracteristica.index');
    Route::get('catalogos/opciones', [opcionescontroller::class, 'index'])->middleware(['auth', 'verified'])->name('catalogos.opciones.index');
    //Paises 
    Route::get('catalogos/paises', [paisescontroller::class, 'index'])->middleware(['auth', 'verified'])->name('catalogos.paises.index');
    //Estados
    Route::get('catalogos/estados', [estadoscontroller::class, 'index'])->middleware(['auth', 'verified'])->name('catalogos.estados.index');
    //Ciudades
    Route::get('catalogos/ciudades', [ciudadescontroller::class, 'index'])->middleware(['auth', 'verified'])->name('catalogos.ciudades.index');
    //Tipo de envvio
    Route::get('catalogos/tipoenvio', [tipoenviocontroller::class, 'index'])->middleware(['auth', 'verified'])->name('catalogos.tipoenvio.index');
    // Tallas 
    Route::get('catalogos/tallas', [TallasController::class, 'tallas'])->middleware(['auth', 'verified'])->name('catalogos.tallas.tallas');
    // Grupos
    Route::get('catalogos/grupos', [TallasController::class, 'grupos'])->middleware(['auth', 'verified'])->name('catalogos.tallas.grupos');

// Prueba data tables

// Prueba de funcionalidad de los web sokets




require __DIR__.'/auth.php';
