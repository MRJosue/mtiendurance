<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TallasController extends Controller
{
    public function tallas(){
        if (!auth()->user()->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
        }
        return view('catalogos.tallas.tallas');
    }


    public function grupos(){
        if (!auth()->user()->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
        }
        return view('catalogos.tallas.grupos');
    }



    public function flujoProduccion(){
        if (!auth()->user()->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
        }
        return view('catalogos.produccion.flujoproduccion');
    }


    public function flujoFiltrosProduccion(){
        if (!auth()->user()->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
        }
        return view('catalogos.produccion.filtrosProduccionvista');
    }
}
