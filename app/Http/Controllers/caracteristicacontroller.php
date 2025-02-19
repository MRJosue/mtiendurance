<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class caracteristicacontroller extends Controller
{
    public function index(){

        if (!auth()->user()->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
        }
        return view('catalogos.caracteristica.index');
    }
}
