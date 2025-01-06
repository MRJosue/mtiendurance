<?php

namespace App\Livewire;

use Livewire\Component;


class DynamicModal extends Component
{
    public $componentName = null;
    public $componentId = null;

    protected $listeners = ['openModal' => 'loadComponent'];

    public function loadComponent($componentName, $componentId = null)
    {
        $this->componentName = $componentName;
        $this->componentId = $componentId;
    }

    public function render()
    {
        return view('livewire.dynamic-modal');
    }
}
