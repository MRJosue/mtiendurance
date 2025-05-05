<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class productocontroller extends Controller
{
    public function index(){

        if (!auth()->user()->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
        }
        return view('catalogos.producto.index');
    }

    public function layout(){

        if (!auth()->user()->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
        }
        return view('catalogos.producto.layout');
    }
}
