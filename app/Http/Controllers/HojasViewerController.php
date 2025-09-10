<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HojaFiltroProduccion;
class HojasViewerController extends Controller
{
    public function show(string $key)
    {
        // Busca por slug o id
        $hoja = HojaFiltroProduccion::query()
            ->where('slug', $key)
            ->orWhere('id', $key)
            ->firstOrFail();

        // Vista contenedor (Blade) que monta el componente Livewire
        return view('produccion.hojas.show', compact('hoja'));
    }
}