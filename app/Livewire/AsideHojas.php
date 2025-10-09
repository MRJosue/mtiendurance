<?php

namespace App\Livewire;

use Livewire\Component;

use App\Models\HojaFiltroProduccion;

class AsideHojas extends Component
{
    /** Ubicación del menú: p.ej. 'pedidos', 'produccion', 'cliente.panel', etc. */
    public string $ubicacion = '';

    /** Mostrar solo las activas en menú (menu_config.activo === true o null) */
    public bool $soloActivas = true;

    public function render()
    {
        $user = auth()->user();

        // Para MySQL conviene ordenar por el orden de menu_config si existe
        // y luego por 'orden' y 'nombre' como fallback.
        $orderExpr = "COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(menu_config, '$.orden')) AS UNSIGNED), orden, 999999)";

        $q = HojaFiltroProduccion::query()
            ->visibles()
            ->accessibleBy($user)
            // Debe tener la ubicación seleccionada
            ->whereJsonContains('menu_config->ubicaciones', $this->ubicacion);

        if ($this->soloActivas) {
            // Si 'activo' viene null, lo tratamos como true (compatibilidad)
            $q->where(function ($qq) {
                $qq->whereNull('menu_config->activo')
                   ->orWhere('menu_config->activo', true);
            });
        }

        $hojas = $q->orderByRaw($orderExpr)
                   ->orderBy('nombre')
                   ->get(['id', 'nombre', 'slug', 'menu_config']);

        return view('livewire.aside-hojas', compact('hojas'));
    }
}