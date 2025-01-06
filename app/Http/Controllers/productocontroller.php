<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class productocontroller extends Controller
{
    public function index(){
        return view('catalogos.producto.index');
    }
}
