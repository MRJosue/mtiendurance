<?php

namespace App\Http\Controllers;
use App\Models\Proyecto;
use Illuminate\Http\Request;

class DisenioController extends Controller
{
    public function index(){

        if (!auth()->user()->can('asidediseniodesplegableAdminTareas')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
        }

         return view('disenio.index');
    }

    public function disenio_detalle(Proyecto $Proyecto){


        return view('disenio.disenio_detalle',  ['proyecto' => $Proyecto]);
    }

    public function admin_tarea(){

        if (!auth()->user()->can('asidediseniodesplegableTareas')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
        }

         return view('disenio.admin_tarea');
    }
}
