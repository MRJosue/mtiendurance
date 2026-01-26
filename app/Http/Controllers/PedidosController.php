<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PedidosController extends Controller
{
    public function index(){
        return view('pedidos.index');
    }

    public function pedidosproveedor(){
        return view('pedidos.pedidosproveedor');
    }
}
