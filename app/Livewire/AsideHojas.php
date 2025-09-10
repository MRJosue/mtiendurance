<?php

namespace App\Livewire;

use Livewire\Component;

use App\Models\HojaFiltroProduccion;
class AsideHojas extends Component
{
    public function render()
    {
        $user = auth()->user();
        $hojas = HojaFiltroProduccion::visibles()
            ->accessibleBy($user)
            ->orderByRaw('COALESCE(orden,999999), nombre')
            ->get(['id','nombre','slug']);
        return view('livewire.aside-hojas', compact('hojas'));
    }
}