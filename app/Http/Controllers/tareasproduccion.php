<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class tareasproduccion extends Controller
{
    public function index(){

        if (!auth()->user()->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
        }

         return view('produccion.tareas');
    }


    public function ordenes_produccion(){

        if (!auth()->user()->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
        }

         return view('produccion.ordenes_produccion');
    }


}
