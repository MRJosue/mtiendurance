<?php

namespace App\Livewire\Proyectos;


use Livewire\Component;
use Livewire\WithPagination; // Importar el trait para paginación
use App\Models\Proyecto;
use App\Models\User;
use App\Models\Tarea;
use App\Models\proyecto_estados;
use App\Notifications\NuevaNotificacion;

use Illuminate\Support\Facades\Auth;
class ManageProjects extends Component
{
        use WithPagination;

        public $perPage = 20;
        public $selectedProjects = [];
        public $selectedProject;
        public $selectAll = false;
        public $mostrarFiltros = false;
        public $modalOpen = false;
    
        public $selectedUser;
        public $taskDescription;

        public $modalVerMas = false;
        public $proyectoSeleccionado = null;

        // Cargamos los diseñadores UNA sola vez
        public $designers;


            /* ---------- Props UI ---------- */
        public array  $tabs      = ['TODOS','PENDIENTE', 'ASIGNADO', 'EN PROCESO', 'REVISION','DISEÑO RECHAZADO', 'DISEÑO APROBADO', 'CANCELADO'];
        public string $activeTab = 'TODOS';              // tab inicial
    

        
        // public $estadosSeleccionados = ['PENDIENTE', 'ASIGNADO', 'EN PROCESO', 'REVISION'];
        public $estadosSeleccionados =[];

            public $estados = [
            'PENDIENTE', 'ASIGNADO', 'EN PROCESO','REVISION', 'DISEÑO APROBADO','DISEÑO RECHAZADO', 'CANCELADO'
        ];


        protected $rules = [
            'selectedUser' => 'required|exists:users,id',
            'taskDescription' => 'required|min:5',
        ];

        protected $messages = [
            'selectedUser.required' => 'Debe seleccionar un usuario.',
            'selectedUser.exists' => 'El usuario seleccionado no es válido.',
            'taskDescription.required' => 'Debe ingresar una descripción.',
            'taskDescription.min' => 'La descripción debe tener al menos 5 caracteres.',
        ];


        public function buscarPorFiltros()
        {
                $this->resetPage();
        }

            
        public function updating($field)
        {
            if ($field === 'perPage') {
                $this->resetPage();
            }
        }


        public function exportSelected()
        {
            // Lógica de exportación
            session()->flash('message', 'Exportación completada.');
        }


        public function updatedSelectAll(bool $value): void
        {
            $this->selectedProjects = $value
                ? Proyecto::when($this->activeTab!=='TODOS', fn($q)=>$q->where('estado',$this->activeTab))
                        ->pluck('id')->toArray()
                : [];
        }


        public function deleteSelected(): void
        {
            Proyecto::whereIn('id', $this->selectedProjects)->delete();
            $this->reset(['selectedProjects', 'selectAll']);
            $this->dispatch('banner', ['message' => 'Proyectos eliminados exitosamente.']);
        }



        public function mount(): void
        {
            // validación de pestaña
            if (!in_array($this->activeTab, $this->tabs, true)) {
                $this->activeTab = $this->tabs[0];
            }

            // Leemos los diseñadores solo 1 vez
            $this->designers = User::whereHas('roles', fn($q) => $q->where('name','diseñador'))->get();
        }

        
        /* ---------- UI handlers ---------- */
        public function setTab(string $tab): void
        {
            $this->activeTab = $tab;
            $this->selectAll = false;
            $this->selectedProjects = [];
            $this->resetPage();
        }

            public function updatingPerPage(): void
        {
            $this->resetPage();
        }


        public function abrirModalAsignacion($projectId)
        {
            $this->selectedProject = Proyecto::find($projectId);
            $this->modalOpen = true;
        }
            public function cerrarModal()
        {
            $this->modalOpen = false;
            $this->selectedUser = null;
            $this->taskDescription = '';
            $this->resetErrorBag();
        }


        public function verMas($proyectoId)
        {
            $this->proyectoSeleccionado = Proyecto::with('estados.usuario')->find($proyectoId);
            $this->modalVerMas = true;
        }

        public function cerrarModalVerMas()
        {
            $this->modalVerMas = false;
            $this->proyectoSeleccionado = null;
        }

        public function asignarTarea()
        {
            $this->validate();

            Tarea::create([
                'proyecto_id' => $this->selectedProject->id,
                'staff_id' => $this->selectedUser,
                'descripcion' => $this->taskDescription,
                'estado' => 'PENDIENTE',
            ]);

            $proyecto = Proyecto::find($this->selectedProject->id);
            if ($proyecto) {
                $proyecto->estado = 'ASIGNADO';
                $proyecto->save();
            }

            proyecto_estados::create([
                'proyecto_id' => $this->selectedProject->id,
                'estado' => "Proyecto asignado a diseñador",
                'fecha_inicio' => now(),
                'usuario_id' => Auth::id(),
            ]);

            $ruta = 'proyectos/' . $this->selectedProject->id;
            $this->enviarNotificacion(Auth::id(), 'Asignaste la tarea del proyecto ' . $this->selectedProject->id, $ruta);
            $this->enviarNotificacion($this->selectedUser, 'Tienes asignado el diseño del proyecto ID: ' . $this->selectedProject->id, $ruta);
            $this->enviarNotificacion($proyecto->usuario_id, 'Cambio de estatus en proyecto: ' . $this->selectedProject->id, $ruta);

            session()->flash('message', 'Tarea asignada exitosamente.');
            $this->cerrarModal();
        }

        public function enviarNotificacion($userId = null, $mensaje = "Tienes una nueva notificación.", $ruta = null)
        {
            $user = $userId ? User::find($userId) : Auth::user();
            $liga = $ruta ? config('app.url') . '/' . $ruta : null;
            if ($user) {
                $user->notify(new NuevaNotificacion($mensaje, $liga));
                $this->dispatch('notificacionEnviada');
            }
        }


        public function render()
        {
            $query = Proyecto::query()
                ->with([
                    // evita 20 consultas a users → 1
                    'user:id,name',
                    // evita 20 consultas a pedidos, productos y categorías → 1 cada una
                    // 'pedidos:id,proyecto_id,producto_id',
                    'pedidos:id,proyecto_id,producto_id,total,estatus',
                    'pedidos.producto:id,nombre,categoria_id',
                    'pedidos.producto.categoria:id,nombre',
                   // si muestras tareas en la tabla
                    'tareas:id,proyecto_id,descripcion',
                    'tareas:id,proyecto_id,staff_id,descripcion,estado',
     
                ]);

            if ($this->activeTab !== 'TODOS') {
                $query->where('estado', $this->activeTab);
            }

            if (!Auth::user()->can('tablaProyectos-ver-todos-los-proyectos')) {
                $query->where('usuario_id', Auth::id());
            }

            $projects = $query->paginate($this->perPage);

            return view('livewire.proyectos.manage-projects', compact('projects'));
        }

}
