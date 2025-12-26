<?php

namespace App\Livewire\Proveedores;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Pedido;
use App\Models\Proyecto;

class PedidosProveedorProyecto extends Component
{
    use WithPagination;

    public int $proyectoId;

    public bool $mostrarFiltros = false;

    public array $filters = [
        'inactivos' => false,
        'estatus_proveedor' => '', // '' = todos
        'solo_no_vistos' => false,
    ];

    // Modal ver/actualizar estatus proveedor
    public bool $modalProveedor = false;
    public ?int $pedidoId = null;

    public ?string $estatus_proveedor = null;
    public ?string $nota_proveedor = null;

    // Estados simples
    public array $estatusProveedorOptions = [
        'PENDIENTE',
        'EN PROCESO',
        'LISTO',
        'BLOQUEADO',
    ];

    protected $listeners = [
        'ActualizarTablaPedidoProveedor' => 'actualizarTabla',
    ];

    public function mount(int $proyectoId)
    {
        $this->proyectoId = $proyectoId;

        // Solo valida que exista el proyecto.
        Proyecto::findOrFail($proyectoId);

        // (Opcional) valida permiso, no proveedor_id:
        // abort_unless(Auth::user()->can('vistaproyectoSeccionPedidos'), 403);
    }

    public function actualizarTabla()
    {
        $this->resetPage();
    }

    public function abrirModalProveedor(int $pedidoId)
    {
        $this->pedidoId = $pedidoId;

        $pedido = $this->baseQuery()
            ->where('id', $pedidoId)
            ->firstOrFail();

        $pedido->update([
            'proveedor_visto_at' => now(),
            'proveedor_visto_por' => Auth::id(),
        ]);

        $this->estatus_proveedor = $pedido->estatus_proveedor ?? 'PENDIENTE';
        $this->nota_proveedor = $pedido->nota_proveedor;

        $this->modalProveedor = true;
    }

    public function guardarProveedor()
    {
        $this->validate([
            'pedidoId' => 'required|integer',
            'estatus_proveedor' => 'required|string|in:' . implode(',', $this->estatusProveedorOptions),
            'nota_proveedor' => 'nullable|string|max:2000',
        ]);

        $pedido = $this->baseQuery()
            ->where('id', $this->pedidoId)
            ->firstOrFail();

        $pedido->update([
            'estatus_proveedor' => $this->estatus_proveedor,
            'nota_proveedor' => $this->nota_proveedor,
            'proveedor_visto_at' => now(),
            'proveedor_visto_por' => Auth::id(),
        ]);

        $this->modalProveedor = false;

        session()->flash('message', '✅ Estatus de proveedor actualizado.');
        $this->dispatch('ActualizarTablaPedidoProveedor');
    }

    /**
     * Query base: solo pedidos del proyecto, tipo PEDIDO,
     * y filtrados a los pedidos asignados a este proveedor (Auth::id()).
     *
     * 🔧 IMPORTANTE:
     * Ajusta el whereHas(...) a tu relación real de asignación.
     */
    // private function baseQuery()
    // {
    //     $proveedorId = Auth::id();

    //     return Pedido::query()
    //         ->where('proyecto_id', $this->proyectoId)
    //         ->where('tipo', 'PEDIDO')
    //         ->whereHas('proyecto', function ($q) use ($proveedorId) {
    //             $q->where('proveedor_id', $proveedorId);
    //         });
    // }

    private function baseQuery()
    {
        return Pedido::query()
            ->where('pedido.proyecto_id', $this->proyectoId)
            ->where('pedido.tipo', 'PEDIDO');
    }


    public function render()
    {
        $proyecto = Proyecto::findOrFail($this->proyectoId);

        $query = $this->baseQuery();

        // activos / inactivos
        if ($this->filters['inactivos']) {
            $query->where('ind_activo', 0);
        } else {
            $query->where('ind_activo', 1);
        }

        // filtro por estatus proveedor
        if (!empty($this->filters['estatus_proveedor'])) {
            $query->where('estatus_proveedor', $this->filters['estatus_proveedor']);
        }

        // solo no vistos
        if (!empty($this->filters['solo_no_vistos'])) {
            $query->whereNull('proveedor_visto_at');
        }

        $query->with([
            'proyecto',
            'producto.categoria',
            'usuario',
            'estadoPedido',
        ]);

        return view('livewire.proveedores.pedidos-proveedor-proyecto', [
            'proyecto' => $proyecto,
            'pedidos'  => $query->orderByDesc('id')->paginate(10),
        ]);
    }

    public function buscarPorFiltros()
    {
        $this->resetPage();
    }

    public function updatedFilters()
    {
        $this->resetPage();
    }
}
