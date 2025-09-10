<?php

namespace App\Livewire\Produccion;

use Livewire\Component;
use App\Models\HojaFiltroProduccion;
use App\Models\Pedido;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\WithPagination;


class HojaViwer extends Component
{
    use WithPagination;

    public HojaFiltroProduccion $hoja;

    #[Url(history:true)]
    public ?int $activeFiltroId = null;

    #[Url(history:true)]
    public string $search = '';

    public int $perPage = 15;

    protected $listeners = [
        'hoja-actualizada' => '$refresh',
        'filtro-produccion-actualizado' => '$refresh',
    ];

    public function mount(HojaFiltroProduccion $hoja): void
    {
        $this->hoja = $hoja;

        if (!$this->activeFiltroId) {
            $first = $hoja->filtros()->first();
            if ($first) $this->activeFiltroId = $first->id;
        }
    }

    public function updatingActiveFiltroId(){ $this->resetPage(); }
    public function updatingSearch(){ $this->resetPage(); }

    public function render()
    {
        $filtros = $this->hoja->filtros()->get(['filtros_produccion.id','filtros_produccion.nombre']);
        $baseCols = $this->hoja->columnasBase()->filter(fn($c)=>$c['key']!=='id' ? ($c['visible'] ?? true) : true)->values();

        $columnasFiltro = collect();
        $productoIds = collect();
        $pedidos = collect();
        $valoresPorPedidoYCar = [];

        if ($this->activeFiltroId) {
            // Carga filtro + columnas (tu método columnas() usa pivot->visible correctamente)
            $filtro = \App\Models\FiltroProduccion::find($this->activeFiltroId);
            if ($filtro) {
                $columnasFiltro = collect($filtro->columnas())->filter(fn($c)=>$c['visible'] ?? true)->sortBy('orden')->values();
                $productoIds = $filtro->productoIds();

                if ($productoIds->isNotEmpty()) {
                    $q = Pedido::query()
                        ->with(['producto:id,nombre','proyecto:id,nombre'])
                        ->whereIn('producto_id', $productoIds)
                        ->where('tipo','PEDIDO');

                    // Filtrar por estados permitidos de la Hoja
                    $allowed = $this->hoja->estados_permitidos ?: [];
                    if (!empty($allowed)) $q->whereIn('estado', $allowed);

                    if ($this->search) {
                        $term = "%{$this->search}%";
                        $q->where(function($sub) use ($term){
                            $sub->whereHas('proyecto', fn($qq)=>$qq->where('nombre','like',$term))
                               ->orWhereHas('producto', fn($qq)=>$qq->where('nombre','like',$term))
                               ->orWhere('estatus','like',$term)
                               ->orWhere('estado','like',$term)
                               ->orWhere('estado_produccion','like',$term);
                        });
                    }

                    $pedidos = $q->latest('id')->paginate($this->perPage);

                    if ($pedidos->count() && $columnasFiltro->count()) {
                        $pedidoIds = $pedidos->pluck('id')->all();
                        $carIds = $columnasFiltro->pluck('id')->all();

                        $rows = DB::table('pedido_opciones as po')
                            ->join('caracteristica_opcion as co','co.opcion_id','=','po.opcion_id')
                            ->join('opciones as o','o.id','=','po.opcion_id')
                            ->whereIn('po.pedido_id',$pedidoIds)
                            ->whereIn('co.caracteristica_id',$carIds)
                            ->get(['po.pedido_id','co.caracteristica_id','o.nombre']);

                        foreach ($rows as $r) {
                            $valoresPorPedidoYCar[$r->pedido_id][$r->caracteristica_id][] = $r->nombre;
                        }
                    }
                }
            }
        }

        return view('livewire.produccion.hoja-viwer', [
            'filtros' => $filtros,
            'baseCols' => $baseCols,
            'columnasFiltro' => $columnasFiltro,
            'productoIds' => $productoIds,
            'pedidos' => $pedidos,
            'valoresPorPedidoYCar' => $valoresPorPedidoYCar,
        ]);
    }


    // public function render()
    // {
    //     return view('livewire.produccion.hoja-viwer');
    // }
}
