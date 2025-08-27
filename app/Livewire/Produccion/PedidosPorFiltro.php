<?php
namespace App\Livewire\Produccion;

use App\Models\FiltroProduccion;
use App\Models\Pedido;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
class PedidosPorFiltro extends Component
{
    use WithPagination;

    // Mantiene la pestaña activa (ID del filtro)
    #[Url(history: true)]
    public ?int $activeFiltroId = null;

    public int $perPage = 15;

    public string $search = ''; // búsqueda básica por proyecto/producto (opcional)

    // Para escuchar cuando el CRUD actualice filtros y refrescar
    protected $listeners = [
        'filtro-produccion-actualizado' => '$refresh',
    ];

    public function mount(): void
    {
        // Si no hay filtro activo, selecciona el primero visible por orden
        if (!$this->activeFiltroId) {
            $first = FiltroProduccion::visibles()
                ->orderByRaw('COALESCE(orden, 999999), id')
                ->first();

            if ($first) {
                $this->activeFiltroId = $first->id;
            }
        }
    }

    public function updatingActiveFiltroId(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        // Lista de filtros visibles para tabs
        $filtros = FiltroProduccion::visibles()
            ->orderByRaw('COALESCE(orden, 999999), id')
            ->get();

        $filtro = null;
        $columnas = collect();
        $productoIds = collect();
        $pedidos = collect();
        $valoresPorPedidoYCar = []; // [pedido_id][caracteristica_id] = [opcion_nombre, ...]

        if ($this->activeFiltroId) {
            $filtro = FiltroProduccion::with(['productos:id,nombre',
              'caracteristicas:id,nombre'])
                ->find($this->activeFiltroId);

            if ($filtro) {
                // Columnas configuradas (características) ya ordenadas
                $columnas = collect($filtro->columnas())
                    ->filter(fn ($c) => $c['visible'] ?? true)
                    ->sortBy('orden')
                    ->values();

                Log::debug('76 Columnas:', ['columnas' =>  $columnas,]);

                // Productos del filtro (estático)
                $productoIds = $filtro->productoIds();

                // Si no hay productos, evita consultar pedidos
                if ($productoIds->isNotEmpty()) {
                    // Pedidos: Solo tipo PEDIDO, filtrado por producto_id ∈ filtro
                    $q = Pedido::query()
                        ->with([
                            'producto:id,nombre',
                            'proyecto:id,nombre',
                            // si tienes más relaciones, agrégalas aquí
                        ])
                        ->whereIn('producto_id', $productoIds)
                        ->where('tipo', 'PEDIDO');

                    if ($this->search) {
                        $term = "%{$this->search}%";
                        $q->where(function ($sub) use ($term) {
                            $sub->whereHas('proyecto', fn($qq) => $qq->where('nombre', 'like', $term))
                               ->orWhereHas('producto', fn($qq) => $qq->where('nombre', 'like', $term))
                               ->orWhere('estatus', 'like', $term)
                               ->orWhere('estado', 'like', $term)
                               ->orWhere('estado_produccion', 'like', $term);
                        });
                    }

                    $pedidos = $q->latest('id')->paginate($this->perPage);

                    // Precalcular valores por característica desde pedido_opciones -> caracteristica_opcion -> opciones
                    if ($pedidos->count() > 0 && $columnas->count() > 0) {
                        $pedidoIds = $pedidos->pluck('id')->all();
                        $carIds = $columnas->pluck('id')->all();
                        
                Log::debug('111 pedidoIds:', ['pedidoIds' =>  $pedidoIds,]);
                Log::debug('112 carIds:', ['carIds' =>  $carIds,]);
                        // Trae filas po (pedido_opciones) unidas con co (caracteristica_opcion) y opciones (para el nombre)
                        $rows = DB::table('pedido_opciones as po')
                            ->join('caracteristica_opcion as co', 'co.opcion_id', '=', 'po.opcion_id')
                            ->join('opciones as o', 'o.id', '=', 'po.opcion_id')
                            ->whereIn('po.pedido_id', $pedidoIds)
                            ->whereIn('co.caracteristica_id', $carIds)
                            ->get(['po.pedido_id', 'co.caracteristica_id', 'o.nombre']);

                        // Agrupar en memoria
                        foreach ($rows as $r) {
                            $valoresPorPedidoYCar[$r->pedido_id][$r->caracteristica_id][] = $r->nombre;
                        }
                    }
                }
            }
        }

        
                Log::debug('129 Columnas:', ['columnas' =>  $columnas,]);
        return view('livewire.produccion.pedidos-por-filtro', [
            'filtros'                 => $filtros,
            'filtro'                  => $filtro,
            'columnas'                => $columnas,
            'productoIds'             => $productoIds,
            'pedidos'                 => $pedidos,
            'valoresPorPedidoYCar'    => $valoresPorPedidoYCar,
        ]);
    }
}



// namespace App\Livewire\Produccion;

// use Livewire\Component;

// class PedidosPorFiltro extends Component
// {
//     public function render()
//     {
//         return view('livewire.produccion.pedidos-por-filtro');
//     }
// }
