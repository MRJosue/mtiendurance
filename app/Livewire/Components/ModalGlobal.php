<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Illuminate\Support\Facades\log;


class ModalGlobal extends Component
{
    public $visible = false;
    public $titulo = '';
    public $mensaje = '';
    public $accion = null;
    public $botonTexto = 'Aceptar';
    public $contenidoVista = null;
    public $props = [];

    protected $listeners = ['mostrarModalGlobal','mostrarModalVista','cerrarModalGlobal'=>'cerrar'];

    public function mostrarModalGlobal($titulo, $mensaje, $accion = null, $botonTexto = 'Aceptar')
    {

         Log::debug('mostrarModalGlobal');
        $this->titulo = $titulo;
        $this->mensaje = $mensaje;
        $this->accion = $accion;
        $this->botonTexto = $botonTexto;
        $this->visible = true;
        
    }

    public function mostrarModalVista($params)
    {
        Log::debug('mostrarModalVista');
        $this->titulo = $params['titulo'] ?? '';
        $this->contenidoVista = $params['vista'] ?? null;
        $this->props = $params['props'] ?? [];
        $this->visible = true;
    }

    public function ejecutarAccion()
    {
        if ($this->accion) {
            $this->dispatch($this->accion);
        }

        $this->cerrar();
    }

    public function cerrar()
    {
        $this->visible = false;
    }

    public function render()
    {
        return view('livewire.components.modal-global');
    }
}