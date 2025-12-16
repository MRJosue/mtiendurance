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


    public function reprogramar(){
        return view('proyectos.reprogramar');
    }


    public function show(Proyecto $Proyecto)
    {

        //dd($Proyecto);
        return view('proyectos.show',  ['proyecto' => $Proyecto]);
    }

    public function showproveedor(Proyecto $Proyecto)
    {

        //proyecto.proveedor.show
        //proyecto.proveedor.show
        return view('proyectos.proveedor.show',  ['proyecto' => $Proyecto]);
    }

    public function transferencias()
    {
        return view('proyectos.transferencias');
    }  

    public function vistaproveedor(Proyecto $Proyecto)
    {

        //dd($Proyecto);
        return view('proyectos.diseñosvistaproveedor',  ['proyecto' => $Proyecto]);
    }


}
