<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SelectMultipleUsuarios extends Component
{
    public string $label;
    public array $opciones;
    public string $entangle;
    public array $seleccionados;


public function __construct($label = 'Selecciona opciones',$opciones = [],$entangle = '', $seleccionados = []) {
    $this->label = $label;
    $this->opciones = $opciones;
    $this->entangle = $entangle;
    $this->seleccionados = $seleccionados;
}

    public function render()
    {
        return view('components.select-multiple-usuarios');
    }
}