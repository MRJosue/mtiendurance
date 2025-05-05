<?php

namespace App\Livewire\Visuales;

use App\Models\Layout;
use Livewire\Component;
use App\Models\Producto;

use Illuminate\Support\Facades\Log;


class LayoutRender extends Component
{
    public $producto_id;
    public $layout;
    public $elementos = [];

    public function mount($producto_id)
    {
        $this->producto_id = $producto_id;

        // Usamos hasOne correctamente desde Producto hacia Layout
        $producto = Producto::with('layout.elementos.caracteristica')->find($producto_id);

        if (!$producto) {
            logger()->debug('Producto no encontrado');
            return;
        }

        if (!$producto->layout) {
            logger()->debug('Producto no tiene layout asignado (hasOne)');
            return;
        }

        $this->layout = $producto->layout;

        $this->elementos = $producto->layout->elementos->map(function ($el) {
            return [
                'id' => $el->id,
                'tipo' => $el->tipo,
                'posicion_x' => $el->posicion_x,
                'posicion_y' => $el->posicion_y,
                'ancho' => $el->ancho,
                'alto' => $el->alto,
                'caracteristica_nombre' => $el->caracteristica->nombre ?? null,
            ];
        })->toArray();

        logger()->debug('Elementos cargados en LayoutRender:', $this->elementos);
    }

    public function render()
    {
        return view('livewire.visuales.layout-render');
    }
}