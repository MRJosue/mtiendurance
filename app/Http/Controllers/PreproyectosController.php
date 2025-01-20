<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PreProyecto;

class PreproyectosController extends Controller
{
    public function index(){
        return view('preproyectos.index');
    }

    public function create(){

        return view('preproyectos.create');
    }

    public function show(PreProyecto $PreProyecto)
    {

        //dd($Proyecto);
        return view('preproyectos.show',  ['preproyecto' => $PreProyecto]);
    }
}
