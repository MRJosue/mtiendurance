<?php

namespace App\Livewire\Components;

use Livewire\Component;

class CardMinimizable extends Component
{
    public $titulo;

    public function __construct($titulo = 'Card')
    {
        $this->titulo = $titulo;
    }

    public function render()
    {
        return view('components.card-minimizable');
    }
}