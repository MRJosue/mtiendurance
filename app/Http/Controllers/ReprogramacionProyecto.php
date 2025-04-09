<?php

namespace App\Http\Controllers;
use App\Models\Proyecto;

use Illuminate\Http\Request;

class ReprogramacionProyecto extends Controller
{
    public function index(Proyecto $Proyecto){

        if (!auth()->user()->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
        }

         return view('reprogramacion.reprogramacionproyectopedido', ['proyecto' => $Proyecto]);
    }


}
