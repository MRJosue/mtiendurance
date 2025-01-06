<?php

namespace App\Livewire;

use Livewire\Component;


class ModalComponent extends Component
{
    public $showModal = false;
    public $id;
    public $component;
    public $titulo;
    public $methodname;

    // Método para mostrar el modal
    public function show()
    {
        $this->showModal = true;
    }

    public function nomunt($id=null,$component = null, $titulo =null, $methodname=null){
        $this->id = $id;
        $this->component = $component;
        $this->titulo = $titulo;
        $this->methodname = $methodname;
    }

    // Método para ocultar el modal
    public function close()
    {
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.modal-component');
    }
}
