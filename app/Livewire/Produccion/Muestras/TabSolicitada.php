<?php

namespace App\Livewire\Produccion\Muestras;

use Livewire\Component;


use Livewire\WithPagination;
use App\Models\Pedido;
use App\Models\PedidoEstado;
use Illuminate\Support\Facades\Auth;

class TabSolicitada extends Component
{
    use WithPagination;

    public array $selected = [];

    public string $estadoColumna = 'SOLICITADA';

    public function marcarSolicitada(array $ids = []): void
    {
        $ids = $ids ?: $this->selected;
        if (empty($ids)) return;

        Pedido::deMuestra()->whereIn('id', $ids)
            ->update(['estatus_muestra' => 'MUESTRA LISTA']);

        // generamos un log 

        // Crea un PedidoEstado por cada pedido
        $pedidos = Pedido::query()
            ->select('id', 'proyecto_id')
            ->whereIn('id', $ids)
            ->get();

        foreach ($pedidos as $pedido) {
            PedidoEstado::create([
                'pedido_id'    => $pedido->id,
                'proyecto_id'  => $pedido->proyecto_id,
                'usuario_id'   => Auth::id(),
                'estado'       => 'MUESTRA LISTA',
                'fecha_inicio' => now(), // opcional, si lo usas en tu flujo
            ]);
        }


        $this->reset('selected');

        // Notificar al padre para refrescar contadores (dispatch en v3)
        // app\Livewire\Produccion\Muestras\AdminMuestrasTabs.php
        //resources\views\livewire\produccion\muestras\admin-muestras-tabs.blade.php
        $this->dispatch('muestraActualizada')->to(\App\Livewire\Produccion\Muestras\AdminMuestrasTabs::class);
    }




    public bool $modalEstadosOpen = false;
    public ?int $pedidoEstadosId = null;

    public array $estadosModal = []; // lo llenamos con arrays para la vista

       public function abrirModalEstados(int $pedidoId): void
    {
        $pedido = Pedido::with(['estados' => fn($q) => $q->with('usuario')->orderByDesc('id')])
            ->findOrFail($pedidoId);

        $this->pedidoEstadosId = $pedidoId;

        // Normalizamos a array para la vista (nombre de usuario incluido)
        $this->estadosModal = $pedido->estados->map(function ($e) {
            return [
                'id'           => $e->id,
                'estado'       => (string) $e->estado,
                'usuario'      => $e->usuario->name ?? '—',
                'usuario_id'   => $e->usuario_id,
                'comentario'   => $e->comentario,
                'fecha_inicio' => optional($e->fecha_inicio)->toDateTimeString(),
                'fecha_fin'    => optional($e->fecha_fin)->toDateTimeString(),
                'created_at'   => optional($e->created_at)->toDateTimeString(),
            ];
        })->toArray();

        $this->modalEstadosOpen = true;
    }

        public function cerrarModalEstados(): void
    {
        $this->reset(['modalEstadosOpen', 'pedidoEstadosId', 'estadosModal']);
    }
    
    public function getPedidosProperty()
    {
        return Pedido::deMuestra()
            ->estatusMuestra('SOLICITADA')
            ->with([
                'producto.categoria',
                'archivo',
            ])
            ->latest('id')
            ->paginate(10);
    }

       private function ultimosPorEstado($pedidos, string $estado)
    {
        $ids = $pedidos->pluck('id');

        return PedidoEstado::with('usuario')
            ->whereIn('pedido_id', $ids)
            ->where('estado', $estado)
            ->orderByDesc('created_at')   // o ->orderByDesc('id') si prefieres
            ->get()
            ->unique('pedido_id')         // nos quedamos con el último por pedido
            ->keyBy('pedido_id');         // mapa: pedido_id => PedidoEstado
    }


        public function render()
    {
        $pedidos = $this->pedidos;
        $ultimosPorEstado = $this->ultimosPorEstado($pedidos, $this->estadoColumna);

        return view('livewire.produccion.muestras.tab-solicitada', [
            'pedidos'          => $pedidos,
            'ultimosPorEstado' => $ultimosPorEstado,
        ]);
    }
}




// class TabSolicitada extends Component
// {
//     public function render()
//     {
//         return view('livewire.produccion.muestras.tab-solicitada');
//     }
// }
