<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ciudadescontroller extends Controller
{
    public function index(){
        return view('catalogos.ciudades.index');
    }
}
