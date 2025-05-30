<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;



class SelectUsuario extends Component
{
    public string $label;
    public array $opciones;
    public string $entangle;
    public ?int $seleccionado;
    public string $onchange;

    public function __construct($label = 'Selecciona un usuario', $opciones = [], $entangle = '', $seleccionado = null, $onchange = 'usuarioSeleccionadoCambio')
    {
        $this->label = $label;
        $this->opciones = $opciones;
        $this->entangle = $entangle;
        $this->seleccionado = $seleccionado;
        $this->onchange = $onchange;
    }

    public function render()
    {
        return view('components.select-usuario');
    }
}