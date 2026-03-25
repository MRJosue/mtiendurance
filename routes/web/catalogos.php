<?php

use App\Http\Controllers\caracteristicacontroller;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ciudadescontroller;
use App\Http\Controllers\estadoscontroller;
use App\Http\Controllers\opcionescontroller;
use App\Http\Controllers\paisescontroller;
use App\Http\Controllers\productocontroller;
use App\Http\Controllers\TallasController;
use App\Http\Controllers\tipoenviocontroller;
use Illuminate\Support\Facades\Route;

Route::get('catalogos/categorias', [CategoriaController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('catalogos.categorias.index');

Route::get('catalogos/producto', [productocontroller::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('catalogos.producto.index');

Route::get('catalogos/producto/layout', [productocontroller::class, 'layout'])
    ->middleware(['auth', 'verified'])
    ->name('catalogos.producto.layout');

Route::get('catalogos/caracteristicas', [caracteristicacontroller::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('catalogos.caracteristica.index');

Route::get('catalogos/opciones', [opcionescontroller::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('catalogos.opciones.index');

Route::get('catalogos/paises', [paisescontroller::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('catalogos.paises.index');

Route::get('catalogos/estados', [estadoscontroller::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('catalogos.estados.index');

Route::get('catalogos/ciudades', [ciudadescontroller::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('catalogos.ciudades.index');

Route::get('catalogos/tipoenvio', [tipoenviocontroller::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('catalogos.tipoenvio.index');

Route::get('catalogos/tallas', [TallasController::class, 'tallas'])
    ->middleware(['auth', 'verified'])
    ->name('catalogos.tallas.tallas');

Route::get('catalogos/grupos', [TallasController::class, 'grupos'])
    ->middleware(['auth', 'verified'])
    ->name('catalogos.tallas.grupos');

Route::get('catalogos/flujoProduccion', [TallasController::class, 'flujoProduccion'])
    ->middleware(['auth', 'verified'])
    ->name('catalogos.flujoProduccion');

Route::get('catalogos/flujoFiltrosProduccion', [TallasController::class, 'flujoFiltrosProduccion'])
    ->middleware(['auth', 'verified'])
    ->name('catalogos.flujoFiltrosProduccion');

Route::get('catalogos/hojaFiltrosProduccion', [TallasController::class, 'hojaFiltrosProduccion'])
    ->middleware(['auth', 'verified'])
    ->name('catalogos.hojaFiltrosProduccion');
