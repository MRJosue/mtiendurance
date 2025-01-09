<?php

namespace App\Livewire\Proyectos;


use App\Models\Tarea;
use App\Models\User;
use Livewire\Component;



class TareasManager extends Component
{
    public $proyectoId;
    public $descripcion;
    public $staffId;
    public $estado = 'PENDIENTE';
    public $tareas = [];
    public $editingTarea;

    protected $rules = [
        'descripcion' => 'required|string|min:3',
        'staffId' => 'required|exists:users,id',
        'estado' => 'required|in:PENDIENTE,EN PROCESO,COMPLETADA',
    ];

    public function mount($proyectoId)
    {
        $this->proyectoId = $proyectoId;
        $this->loadTareas();
    }

    public function loadTareas()
    {
        $this->tareas = Tarea::where('proyecto_id', $this->proyectoId)->with('staff')->get()->toArray();
    }

    public function saveTarea()
    {
        $this->validate();

        Tarea::create([
            'proyecto_id' => $this->proyectoId,
            'staff_id' => $this->staffId,
            'descripcion' => $this->descripcion,
            'estado' => $this->estado,
        ]);

        $this->reset(['descripcion', 'staffId', 'estado']);
        $this->loadTareas();
        session()->flash('message', 'Tarea creada exitosamente.');
    }

    public function editTarea($id)
    {
        $tarea = Tarea::findOrFail($id);
        $this->editingTarea = $tarea;
        $this->descripcion = $tarea->descripcion;
        $this->staffId = $tarea->staff_id;
        $this->estado = $tarea->estado;
    }

    public function updateTarea()
    {
        $this->validate();

        $this->editingTarea->update([
            'descripcion' => $this->descripcion,
            'staff_id' => $this->staffId,
            'estado' => $this->estado,
        ]);

        $this->reset(['descripcion', 'staffId', 'estado', 'editingTarea']);
        $this->loadTareas();
        session()->flash('message', 'Tarea actualizada exitosamente.');
    }

    public function deleteTarea($id)
    {
        Tarea::findOrFail($id)->delete();
        $this->loadTareas();
        session()->flash('message', 'Tarea eliminada exitosamente.');
    }

    public function render()
    {
        return view('livewire.proyectos.tareas-manager', [
            'usuarios' => User::all(),
        ]);
    }
}



        //return view('livewire.proyectos.tareas-manager');
