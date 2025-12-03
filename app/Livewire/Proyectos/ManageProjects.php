<?php

namespace App\Livewire\Proyectos;


use Livewire\Component;
use Livewire\WithPagination; 
use App\Models\Proyecto;
use App\Models\User;
use App\Models\Tarea;
use App\Models\proyecto_estados;
use App\Notifications\NuevaNotificacion;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;

use Illuminate\Support\Facades\Auth;
class ManageProjects extends Component
{
        use WithPagination;

        public $perPage = 20;
        public array $perPageOptions = [10, 20, 30, 50, 100];


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

         public array $subordinateIds = [];
        public bool  $isClientePrincipalConSub = false;


            // ORDENAMIENTO
            public string $sortField = 'id';
            public string $sortDir   = 'desc';
            protected array $sortable = ['id','nombre','estado']; // whitelist
            



            /* ---------- Props UI ---------- */
        public array  $tabs      = ['TODOS','PENDIENTE', 'ASIGNADO', 'EN PROCESO', 'REVISION','DISEÑO RECHAZADO', 'DISEÑO APROBADO', 'CANCELADO', 'REPROGRAMAR'];
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

        
        public array $filters = [
            'id'        => null,
            'nombre'    => null,
            'cliente'   => null,
            'estado'    => null,
            'inactivos' => false, // 👈 bool
        ];


        public function updatedPerPage($value): void
        {
            $value = (int) $value;
            if (! in_array($value, $this->perPageOptions, true)) {
                $value = 20;
            }
            $this->perPage = $value;
            $this->resetPage();
        }

        public function sortBy(string $field): void
        {
            if (!in_array($field, $this->sortable, true)) return;

            // toggle dirección si es el mismo campo
            if ($this->sortField === $field) {
                $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                $this->sortField = $field;
                $this->sortDir   = 'asc';
            }
            $this->resetPage();
        }


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
                'inactivos' => false, // 👈 reset también
            ];
            $this->resetPage();
            $this->dispatch('filters-cleared');
        }

        public function updatedFilters($value): void
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
            if (!$value) return;

            $idsQuery = Proyecto::query()

                // 👇 mismo filtro base
                ->when($this->filters['inactivos'], fn($q) =>
                    $q->where('ind_activo', 0)
                )
                ->when(!$this->filters['inactivos'], fn($q) =>
                    $q->where('ind_activo', 1)
                )
                
                // Tab REPROGRAMAR
                ->when($this->activeTab === 'REPROGRAMAR', fn($q) =>
                    $q->where('estado', 'DISEÑO APROBADO')->where('flag_reconfigurar', 1)
                )
                // Estados normales
                ->when(in_array($this->activeTab, $this->estados, true), fn($q) =>
                    $q->where('estado', $this->activeTab)
                )
                // Filtro adicional para DISEÑO APROBADO
                ->when($this->activeTab === 'DISEÑO APROBADO', function ($q) {
                    $q->whereHas('pedidos', fn($sub) =>
                        $sub->where('tipo', 'PEDIDO')->where('estado_id', '1')
                    );
                });

            // Restricción por rol también aquí
            $user = Auth::user();
            if ($user->hasRole('admin')) {
                // sin restricción extra
            } elseif ($user->hasRole('cliente_principal')) {
                $idsUsuarios = array_values(array_unique(array_merge([$user->id], $this->subordinateIds)));
                $idsQuery->whereIn('usuario_id', $idsUsuarios);
            } elseif ($user->hasAnyRole(['cliente_subordinado','estaf'])) {
                $idsQuery->where('usuario_id', $user->id);
            } else {
                $idsQuery->where('usuario_id', $user->id);
            }

            $this->selectedProjects = $idsQuery->pluck('id')->toArray();
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

            // Diseñadores una vez
            $this->designers = User::whereHas('roles', fn($q) => $q->where('name','diseñador'))->get();

            // Cargar subordinados desde el JSON del usuario autenticado
            $user = Auth::user();

            if ($user->hasRole('cliente_principal')) {
                // Asegura enteros y filtra null/strings vacíos
                $this->subordinateIds = collect($user->subordinados ?? [])
                    ->map(fn($id) => (int) $id)
                    ->filter(fn($id) => $id > 0)
                    ->values()
                    ->all();

                $this->isClientePrincipalConSub = count($this->subordinateIds) > 0;
            }

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

                    'user:id,name,empresa_id,sucursal_id',
                    'user.empresa:id,nombre',
                    'user.sucursal:id,nombre,empresa_id',
                    'user.sucursal.empresa:id,nombre',

                    
                    'pedidos:id,proyecto_id,producto_id,total,estatus',
                    'pedidos.producto:id,nombre,categoria_id',
                    'pedidos.producto.categoria:id,nombre',
                    'tareas:id,proyecto_id,staff_id,descripcion,estado',
                ]);
            
                // --- Filtro base activo / inactivo ---
                if ($this->filters['inactivos']) {
                    // Si está activo el check => solo inactivos
                    $query->where('ind_activo', 0);
                } else {
                    // Sin check => solo activos
                    $query->where('ind_activo', 1);
                }

            // --- Filtros por columna ---
            $query
                ->when($this->filters['id'], function ($q, $v) {
                    $ids = collect(preg_split('/[,;\s]+/', (string)$v, -1, PREG_SPLIT_NO_EMPTY))
                        ->map(fn($i) => (int)trim($i))
                        ->filter();
                    if ($ids->count() === 1) $q->where('id', $ids->first());
                    elseif ($ids->isNotEmpty()) $q->whereIn('id', $ids->all());
                })
                ->when($this->filters['nombre'], fn($q, $v) =>
                    $q->where('nombre', 'like', '%'.$v.'%')
                )
                ->when($this->filters['cliente'] && Auth::user()->can('tablaProyectos-ver-todos-los-proyectos'), function ($q) {
                    $v = trim((string)$this->filters['cliente']);
                    $q->whereHas('user', fn($u) =>
                        $u->where('name', 'like', '%'.$v.'%')->orWhere('email', 'like', '%'.$v.'%')
                    );
                })

                ->when($this->filters['estado'], fn($q, $v) =>
                    $q->where('estado', $v)
                )

                ->when($this->filters['inactivos'], function ($q) {
                    $q->where('ind_activo', 0);
                });


            // --- Tabs ---
            if ($this->activeTab === 'REPROGRAMAR') {
                $query->where('estado', 'DISEÑO APROBADO')->where('flag_reconfigurar', 1);
            }
            if (in_array($this->activeTab, $this->estados, true)) {
                $query->where('estado', $this->activeTab);
            }
            if ($this->activeTab === 'DISEÑO APROBADO') {
                $query->whereHas('pedidos', fn($q) =>
                    $q->where('tipo','PEDIDO')->where('estado_id','1')
                );
            }

            // --- Restricción por rol/permiso (UNA sola vez) ---
            $user = Auth::user();
            if ($user->hasRole('admin') || $user->can('tablaProyectos-ver-todos-los-proyectos')) {
                // ve todo
            } elseif ($user->hasRole('cliente_principal')) {
                $idsUsuarios = array_values(array_unique(array_merge([$user->id], $this->subordinateIds)));
                $query->whereIn('usuario_id', $idsUsuarios);
            } else {
                // cliente_subordinado, estaf, u otros
                $query->where('usuario_id', $user->id);
            }

            

            // --- Order by (seguro) ---
            if (!in_array($this->sortField, $this->sortable, true)) {
                $this->sortField = 'id';
            }
            $query->orderBy($this->sortField, $this->sortDir);

            // --- Paginar solo una vez ---
            $projects = $query->paginate($this->perPage);

            return view('livewire.proyectos.manage-projects', compact('projects'));
        }


}
