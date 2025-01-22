<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class estadoscontroller extends Controller
{

    public function index(){
        return view('catalogos.estados.index');
    }


}
