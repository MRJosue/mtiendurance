<?php

namespace App\Livewire\Proyectos;

use Illuminate\Validation\Rule;

use Livewire\Component;
use App\Models\Proyecto; // Asegúrate de tener este modelo
use App\Models\User; // Asegúrate de tener este modelo

class ManageProjects extends Component
{
    public $projects;

    public function mount()
    {

        $this->projects = Proyecto::with('user')->get();
    }

    public function render()
    {
        return view('livewire.proyectos.manage-projects');
    }
}
