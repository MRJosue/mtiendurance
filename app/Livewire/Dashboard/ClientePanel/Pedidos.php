<?php

namespace App\Livewire\Dashboard\ClientePanel;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Pedido;
use Illuminate\Support\Facades\Log;

class Pedidos extends Component
{
    use WithPagination;

    /* ---- UI: pestañas PEDIDOS | MUESTRAS ---- */
    public array  $tabs      = ['PEDIDOS', 'MUESTRAS'];
    public string $activeTab = 'PEDIDOS';

    /* ---- Filtros extra que ya tenías ---- */
    public bool $mostrarFiltros          = false;
    public bool $mostrarSoloNoAprobados  = true;

    public $modalVerInfo = false;
    public $infoProyecto = null;

    /* ---- Manipuladores de UI ---- */
    public function setTab(string $tab): void
    {
        if ($this->activeTab !== $tab) {
            $this->activeTab = $tab;
            $this->resetPage();   // reinicia paginación al cambiar de pestaña
        }
    }

    public function buscarPorFiltros(): void
    {
        $this->resetPage();
    }


    public function abrirModalVerInfo($proyectoId)
    {
        Log::debug('Datos del usuario procesados Open modal');

$proyecto = \App\Models\Proyecto::with([
    'user',
    'categoria',
])->findOrFail($proyectoId);

        // Asegúrate de que los campos sean arrays
        $proyecto->caracteristicas_sel = is_array($proyecto->caracteristicas_sel) ? $proyecto->caracteristicas_sel : json_decode($proyecto->caracteristicas_sel, true);
        $proyecto->producto_sel        = is_array($proyecto->producto_sel)        ? $proyecto->producto_sel        : json_decode($proyecto->producto_sel, true);
        $proyecto->categoria_sel       = is_array($proyecto->categoria_sel)       ? $proyecto->categoria_sel       : json_decode($proyecto->categoria_sel, true);
        
        $this->infoProyecto = $proyecto;
        $this->modalVerInfo = true;
    }

    /* ---- Render ---- */
    public function render()
    {
        $query = Pedido::with([
            'producto.categoria',
            'proyecto.user',
            'pedidoCaracteristicas.caracteristica',
            'pedidoOpciones.opcion.caracteristicas',
        ])
        ->where('tipo', $this->activeTab === 'MUESTRAS' ? 'MUESTRA' : 'PEDIDO');

        if (!auth()->user()->can('tablaPedidos-ver-todos-los-pedidos')) {
            $query->whereHas('proyecto', fn($q) => $q->where('usuario_id', auth()->id()));
        }

        // if ($this->mostrarSoloNoAprobados) {
        //     // Proyectos cuyo diseño NO esté aprobado
        //     $query->whereHas('proyecto', fn($q) => $q->where('estado', '!=', 'DISEÑO APROBADO'));
        // } else {
        //     // Solo los aprobados
        //     $query->whereHas('proyecto', fn($q) => $q->where('estado', 'DISEÑO APROBADO'));
        // }

        return view('livewire.dashboard.cliente-panel.pedidos', [
            'pedidos' => $query->orderByDesc('created_at')->paginate(10),
        ]);
    }
}
