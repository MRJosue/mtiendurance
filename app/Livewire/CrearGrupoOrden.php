<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\GrupoOrden;

class CrearGrupoOrden extends Component
{
    public $nombre;

    protected $rules = [
        'nombre' => 'required|string|min:2|unique:grupos_orden,nombre',
    ];

    public function guardar()
    {
        $this->validate();

        GrupoOrden::create([
            'nombre' => $this->nombre,
        ]);

        session()->flash('message', 'Grupo creado exitosamente.');
        $this->reset('nombre');
        $this->dispatch('grupoOrdenCreado'); // puedes usar esto para refrescar otro componente si lo necesitas
    }

    public function render()
    {
        return view('livewire.crear-grupo-orden');
    }
}
