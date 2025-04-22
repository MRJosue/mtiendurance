<?php

namespace App\Livewire\Proyectos;

use Livewire\Component;

class MockupVisual extends Component
{
    public $color = '#ff0000';
    public $mostrar_logo = true;

    public function render()
    {
        return view('livewire.proyectos.mockup-visual');
    }
}