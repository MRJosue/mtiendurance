<?php

use App\Http\Controllers\HojasViewerController;
use App\Http\Controllers\ProduccionController;
use App\Http\Controllers\ProgramacionController;
use App\Http\Controllers\tareasproduccion;
use Illuminate\Support\Facades\Route;

Route::get('/programacion', [ProgramacionController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('programacion.index');

Route::get('/produccion/Administraciondepedidos', [ProduccionController::class, 'adminpedidos'])
    ->middleware(['auth', 'verified'])
    ->name('produccion.adminpedidos');

Route::get('/produccion/Administraciondemuestras', [ProduccionController::class, 'adminmuestras'])
    ->middleware(['auth', 'verified'])
    ->name('produccion.adminmuestras');

Route::get('/produccion/aprobacion_especial', [tareasproduccion::class, 'aprobacion_especial'])
    ->middleware(['auth', 'verified'])
    ->name('produccion.aprobacion_especial');

Route::get('/produccion/ordenes_produccion', [tareasproduccion::class, 'ordenes_produccion'])
    ->middleware(['auth', 'verified'])
    ->name('produccion.ordenes_produccion');

Route::get('/produccion/ordenes_produccion/imprimir/{orden}', [tareasproduccion::class, 'imprimirOrdenProduccion'])
    ->middleware(['auth', 'verified'])
    ->name('produccion.ordenes_produccion.imprimir');

Route::get('/produccion/tareas', [tareasproduccion::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('produccion.tareas');

Route::get('/produccion/corte', [tareasproduccion::class, 'corte'])
    ->middleware(['auth', 'verified'])
    ->name('produccion.corte');

Route::get('/produccion/sublimado', [tareasproduccion::class, 'sublimado'])
    ->middleware(['auth', 'verified'])
    ->name('produccion.sublimado');

Route::get('/produccion/costura', [tareasproduccion::class, 'costura'])
    ->middleware(['auth', 'verified'])
    ->name('produccion.costura');

Route::get('/produccion/maquila', [tareasproduccion::class, 'maquila'])
    ->middleware(['auth', 'verified'])
    ->name('produccion.maquila');

Route::get('/produccion/facturacion', [tareasproduccion::class, 'facturacion'])
    ->middleware(['auth', 'verified'])
    ->name('produccion.facturacion');

Route::get('/produccion/entrega', [tareasproduccion::class, 'entrega'])
    ->middleware(['auth', 'verified'])
    ->name('produccion.entrega');

Route::middleware(['auth'])->group(function () {
    Route::get('/produccion/hojas/{key}', [HojasViewerController::class, 'show'])
        ->name('produccion.hojas.show');
});
