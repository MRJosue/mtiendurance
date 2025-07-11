<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrdenProduccion;
use Barryvdh\DomPDF\Facade\Pdf;

class tareasproduccion extends Controller
{
    public function index(){

        if (!auth()->user()->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
        }

         return view('produccion.tareas');
    }


    public function aprobacion_especial(){

        if (!auth()->user()->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
        }

         return view('produccion.aprobacion_especial');
    }


    public function ordenes_produccion(){

        if (!auth()->user()->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a esta sección.');
        }

         return view('produccion.ordenes_produccion');
    }

    public function imprimirOrdenProduccion($ordenId)
    {
        $orden = OrdenProduccion::with(['pedidos.producto', 'ordenCorte'])->findOrFail($ordenId);
    
        return PDF::loadView('pdf.orden_produccion', [
            'orden' => $orden
        ])->stream("orden_produccion_{$ordenId}.pdf");
    }

}
