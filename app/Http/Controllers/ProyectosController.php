<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proyecto;

class ProyectosController extends Controller
{
    public function index(){
        return view('proyectos.index');
    }

    public function show(Proyecto $Proyecto)
    {

        //dd($Proyecto);
        return view('Proyectos.show',  ['proyecto' => $Proyecto]);
    }
}
