<?php

namespace App\Livewire\Components;

use Livewire\Component;

class TableActions extends Component
{

    public $selectedId;  // Aquí guardamos el ID seleccionado para el modal

    // Escucha el evento 'openModal' emitido desde la tabla
    protected $listeners = ['openModal'];

    // Este método se ejecuta cuando se emite el evento 'openModal'
    public function openModal($id)
    {
        $this->selectedId = $id;  // Asignamos el ID seleccionado

        // Aquí podrías hacer otras acciones, como cargar más datos, si es necesario.

        // Emitimos un evento para abrir el modal en el navegador.
        $this->dispatchBrowserEvent('open-modal');
    }

    public function render()
    {
        return view('livewire.components.table-actions');
    }
}
