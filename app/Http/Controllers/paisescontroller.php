<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class paisescontroller extends Controller
{
    public function index(){
        return view('catalogos.paises.index');
    }
}
