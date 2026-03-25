<?php

use App\Http\Controllers\PreproyectoUploadController;
use App\Http\Controllers\PreproyectosController;
use Illuminate\Support\Facades\Route;

Route::get('/preproyectos', [PreproyectosController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('preproyectos.index');

Route::get('/preproyectos/show/{preproyecto}', [PreproyectosController::class, 'show'])
    ->middleware(['auth', 'verified'])
    ->name('preproyectos.show');

Route::get('/preproyectos/create', [PreproyectosController::class, 'create'])
    ->middleware(['auth', 'verified'])
    ->name('preproyectos.create');

Route::post('/preproyecto/upload-temporal', [PreproyectoUploadController::class, 'upload'])
    ->middleware(['auth', 'verified'])
    ->name('preproyecto.upload-temporal');
