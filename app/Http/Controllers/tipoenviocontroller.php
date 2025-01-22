<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class tipoenviocontroller extends Controller
{
    public function index(){
        return view('catalogos.tipoenvio.index');
    }
}
