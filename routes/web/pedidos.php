<?php

use App\Http\Controllers\PedidosController;
use Illuminate\Support\Facades\Route;

Route::get('/pedidos', [PedidosController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('pedidos.index');

Route::get('/pedidos/proveedor', [PedidosController::class, 'pedidosproveedor'])
    ->middleware(['auth', 'verified'])
    ->name('pedidos.pedidosproveedor');
