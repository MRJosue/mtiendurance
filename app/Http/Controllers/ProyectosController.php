<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proyecto;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;

class ProyectosController extends Controller
{
    public function index(){
        return view('proyectos.index');
    }

    public function show(Proyecto $Proyecto)
    {

        //dd($Proyecto);
        return view('proyectos.show',  ['proyecto' => $Proyecto]);
    }




}
