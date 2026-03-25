<?php

use App\Http\Controllers\ProyectosController;
use App\Http\Controllers\ReprogramacionProyecto;
use Illuminate\Support\Facades\Route;

Route::get('/proyectos', [ProyectosController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('proyectos.index');

Route::get('/proyectos/transferencias', [ProyectosController::class, 'transferencias'])
    ->middleware(['auth', 'verified'])
    ->name('proyectos.transferencias');

Route::get('/proyectos/reprogramar', [ProyectosController::class, 'reprogramar'])
    ->middleware(['auth', 'verified'])
    ->name('proyectos.reprogramar');

Route::get('/proyectos/{proyecto}', [ProyectosController::class, 'show'])
    ->middleware(['auth', 'verified', 'proyecto.access'])
    ->name('proyecto.show');

Route::get('/proveedor/proyectos/{proyecto}', [ProyectosController::class, 'showproveedor'])
    ->middleware(['auth', 'verified'])
    ->name('proyecto.proveedor.show');

Route::get('/proveedores/diseños', [ProyectosController::class, 'vistaproveedor'])
    ->middleware(['auth', 'verified'])
    ->name('diseños.vistaproveedor');

Route::get('/reprogramacion/{proyecto}', [ReprogramacionProyecto::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('reprogramacion.reprogramacionproyectopedido');
