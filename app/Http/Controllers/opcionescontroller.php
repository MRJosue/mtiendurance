<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class opcionescontroller extends Controller
{
    public function index(){
        return view('catalogos.opcion.index');
    }
}
