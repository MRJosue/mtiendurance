<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class ProduccionController extends Controller
{
    public function adminpedidos(){

        // if (!auth()->user()->hasRole('admin')) {
        if (!auth()->user()->can('asideAdministraciónPedidos')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
        }

         return view('produccion.adminpedidos');
    }

    public function adminmuestras(){

        if (!auth()->user()->can('asideAdministraciónMuestras')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
        }

         return view('produccion.adminmuestras');
    }

}
