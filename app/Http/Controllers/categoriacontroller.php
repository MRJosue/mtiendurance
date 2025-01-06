<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class categoriacontroller extends Controller
{
    public function index(){

        return view('catalogos.categoria.index');
    }
}
