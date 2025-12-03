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
class ReconfigurarProyectos extends Component
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


        public bool $modalResumen = false;
        public ?int $proyectoResumenId = null;
        public ?\App\Models\Pedido $ultimoPedidoPendiente = null;
        /** @var \Illuminate\Support\Collection<int,\App\Models\Pedido> */
        public $ultimosPedidos;
        /** @var \App\Models\Proyecto|null */
        public $proyectoResumen = null;


            /* ---------- Props UI ---------- */
        public array  $tabs      = [ 'POR REPROGRAMAR'];
        public string $activeTab = 'TODOS';              // tab inicial
    

        
        // public $estadosSeleccionados = ['PENDIENTE', 'ASIGNADO', 'EN PROCESO', 'REVISION'];
        public $estadosSeleccionados =[];

            public $estados = [
            'PENDIENTE', 'ASIGNADO', 'EN PROCESO', 'REVISION', 'DISEÑO APROBADO','DISEÑO RECHAZADO', 'CANCELADO'
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

        
public array $filters = [
    'id'        => null,
    'nombre'    => null,
    'cliente'   => null,
    'estado'    => null,
    'inactivos' => false, // 👈 NUEVO: false = solo activos, true = solo inactivos
];



        public function buscarPorFiltros()
        {
                $this->resetPage();
        }

        public function applyFilters(): void
        {
            $this->resetPage();
        }

        public function clearFilters(): void
        {
            $this->filters = [
                'id'        => null,
                'nombre'    => null,
                'cliente'   => null,
                'estado'    => null,
                'inactivos' => false,
            ];
            $this->resetPage();
            $this->dispatch('filters-cleared');
        }

        public function updatedFilters(): void
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
            $this->selectedProjects = [];

            if (!$value) {
                return;
            }

            $ids = Proyecto::query()

                ->when($this->filters['inactivos'] ?? false, function ($q) {
                     $q->where('ind_activo', 0);
                })
                ->when(!($this->filters['inactivos'] ?? false), function ($q) {
                    $q->where('ind_activo', 1);
                })

                // NUEVO: Tab POR REPROGRAMAR => ambos flags en 1
                ->when($this->activeTab === 'POR REPROGRAMAR', function ($q) {
                    $q->where('flag_solicitud_reconfigurar', 1)
                    ->where('flag_reconfigurar', 1);
                })
                // Ya existía: REPROGRAMAR => aprobado + flag_reconfigurar = 1
                ->when($this->activeTab === 'REPROGRAMAR', function ($q) {
                    $q->where('estado', 'DISEÑO APROBADO')
                    ->where('flag_reconfigurar', 1);
                })
                // Estados “normales”
                ->when(in_array($this->activeTab, $this->estados, true), fn ($q) =>
                    $q->where('estado', $this->activeTab)
                )
                // Filtro adicional para “DISEÑO APROBADO”
                ->when($this->activeTab === 'DISEÑO APROBADO', function ($q) {
                    $q->whereHas('pedidos', function ($sub) {
                        $sub->where('tipo', 'PEDIDO')
                            ->where('estado_id', '1');
                    });
                })
                ->pluck('id')
                ->toArray();

            $this->selectedProjects = $ids;
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


        public function abrirResumenPedidos(int $proyectoId): void
        {
            $this->proyectoResumenId = $proyectoId;

            // Cargar proyecto y relaciones mínimas para el resumen
            $this->proyectoResumen = Proyecto::select('id','nombre')
                ->find($proyectoId);

            // Último pedido con estatus PENDIENTE
            $this->ultimoPedidoPendiente = \App\Models\Pedido::with([
                    'producto:id,nombre,categoria_id',
                    'producto.categoria:id,nombre',
                ])
                ->where('proyecto_id', $proyectoId)
                ->where('tipo', 'PEDIDO')
                ->where('estado_id', '1')
                ->latest('id')
                ->first();

            // Últimos 5 pedidos (para lista compacta)
            $this->ultimosPedidos = \App\Models\Pedido::with([
                    'producto:id,nombre,categoria_id',
                    'producto.categoria:id,nombre',
                ])
                ->where('proyecto_id', $proyectoId)
                ->where('tipo', 'PEDIDO')
                ->where('estado_id', '1')
                ->latest('id')
                ->take(5)
                ->get();

            $this->modalResumen = true;
        }

        public function cerrarResumenPedidos(): void
        {
            $this->modalResumen         = false;
            $this->proyectoResumenId    = null;
            $this->proyectoResumen      = null;
            $this->ultimoPedidoPendiente = null;
            $this->ultimosPedidos       = collect();
        }


        public function render()
        {
            $query = Proyecto::query()
                ->with([
                    'user:id,name',
                    'pedidos:id,proyecto_id,producto_id,total,estatus',
                    'pedidos.producto:id,nombre,categoria_id',
                    'pedidos.producto.categoria:id,nombre',
                    'tareas:id,proyecto_id,staff_id,descripcion,estado',
                ]);


            $query
            ->when($this->filters['inactivos'] ?? false, function ($q) {
                // check marcado → solo inactivos
                $q->where('ind_activo', 0);
            })
            ->when(!($this->filters['inactivos'] ?? false), function ($q) {
                // sin check → solo activos
                $q->where('ind_activo', 1);
            });

            // Filtros por columna (se quedan igual) ...
            $query
                ->when($this->filters['id'], function ($q, $v) {
                    $ids = collect(preg_split('/[,;\s]+/', (string)$v, -1, PREG_SPLIT_NO_EMPTY))
                        ->map(fn($i) => (int)trim($i))
                        ->filter();
                    if ($ids->count() === 1) {
                        $q->where('id', $ids->first());
                    } elseif ($ids->isNotEmpty()) {
                        $q->whereIn('id', $ids->all());
                    }
                })
                ->when($this->filters['nombre'], fn($q, $v) =>
                    $q->where('nombre', 'like', '%'.$v.'%')
                )
                ->when($this->filters['cliente'] && \Illuminate\Support\Facades\Auth::user()->can('tablaProyectos-ver-todos-los-proyectos'), function ($q) {
                    $v = trim((string)$this->filters['cliente']);
                    $q->whereHas('user', fn($u) =>
                        $u->where('name', 'like', '%'.$v.'%')
                        ->orWhere('email', 'like', '%'.$v.'%')
                    );
                })
                ->when($this->filters['estado'], fn($q, $v) =>
                    $q->where('estado', $v)
                );

            /* ====== NUEVO: Tab POR REPROGRAMAR ======
            Muestra todos los proyectos con ambos flags en 1,
            sin importar el estado. */
            if ($this->activeTab === 'POR REPROGRAMAR') {
                $query->where('flag_solicitud_reconfigurar', 1)
                    ->where('flag_reconfigurar', 1);
            }

            // Ya existía: REPROGRAMAR => DISEÑO APROBADO + flag_reconfigurar = 1
            if ($this->activeTab === 'REPROGRAMAR') {
                $query->where('estado', 'DISEÑO APROBADO')
                    ->where('flag_reconfigurar', 1);
            }

            // Estados “normales” (solo si el tab está en la lista de estados)
            if (in_array($this->activeTab, $this->estados, true)) {
                $query->where('estado', $this->activeTab);
            }

            // Filtro permanente adicional para el tab “DISEÑO APROBADO”
            if ($this->activeTab === 'DISEÑO APROBADO') {
                $query->whereHas('pedidos', function ($q) {
                    $q->where('tipo', 'PEDIDO')
                    ->where('estado_id', '1');
                });
            }

            // Restricción por permisos
            if (!\Illuminate\Support\Facades\Auth::user()->can('tablaProyectos-ver-todos-los-proyectos')) {
                $query->where('usuario_id', \Illuminate\Support\Facades\Auth::id());
            }

            $projects = $query->paginate($this->perPage);

            return view('livewire.proyectos.reconfigurar-proyectos', compact('projects'));
        }



}


// class ReconfigurarProyectos extends Component
// {
//     public function render()
//     {
//         return view('livewire.proyectos.reconfigurar-proyectos');
//     }
// }
