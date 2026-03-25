<?php

use App\Http\Controllers\DisenioController;
use Illuminate\Support\Facades\Route;

Route::get('/diseño', [DisenioController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('disenio.index');

Route::get('/diseño/disenio_detalle/{proyecto}', [DisenioController::class, 'disenio_detalle'])
    ->middleware(['auth', 'verified'])
    ->name('disenio.disenio_detalle');

Route::get('/diseño/admin_tarea', [DisenioController::class, 'admin_tarea'])
    ->middleware(['auth', 'verified'])
    ->name('disenio.admin_tarea');
