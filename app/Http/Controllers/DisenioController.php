<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DisenioController extends Controller
{
    public function index(){

        if (!auth()->user()->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
        }

         return view('disenio.index');
    }

    public function disenio_detalle(){

        if (!auth()->user()->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
        }

         return view('disenio.disenio_detalle');
    }

    public function admin_tarea(){

        if (!auth()->user()->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
        }

         return view('disenio.admin_tarea');
    }
}
