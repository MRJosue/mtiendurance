<?php

namespace App\Livewire\Produccion\Muestras;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Pedido;
use App\Models\PedidoEstado;
use Illuminate\Support\Facades\Auth;

class TabCancelada extends Component
{
    use WithPagination;

    public array $selected = [];
    public string $estadoColumna = 'CANCELADA';

    /** Filtros */
    public bool $mostrarFiltros = true;
    public string $f_id = '';
    public string $f_producto = '';
    public string $f_cliente = '';
    public string $f_archivo = '';
    public string $f_total_min = '';
    public string $f_usuario = '';
    public string $f_instrucciones = '';
    public string $f_estatus = '';

    /** Persistencia en URL */
    protected $queryString = [
        'f_id' => ['except' => ''],
        'f_producto' => ['except' => ''],
        'f_cliente' => ['except' => ''],
        'f_archivo' => ['except' => ''],
        'f_total_min' => ['except' => ''],
        'f_usuario' => ['except' => ''],
        'f_instrucciones' => ['except' => ''],
        'f_estatus' => ['except' => ''],
        'page' => ['except' => 1],
        'mostrarFiltros' => ['except' => true],
    ];

    public function updated($prop): void
    {
        if (str_starts_with($prop, 'f_')) {
            $this->resetPage();
        }
    }

    public function buscarPorFiltros(): void
    {
        $this->resetPage();
    }

    public function marcarSolicitada(array $ids = []): void
    {
        $ids = $ids ?: $this->selected;
        if (empty($ids)) return;

        Pedido::deMuestra()->whereIn('id', $ids)
            ->update(['estatus_muestra' => 'CANCELADA']);

        $this->reset('selected');

        $pedidos = Pedido::query()
            ->select('id', 'proyecto_id')
            ->whereIn('id', $ids)
            ->get();

        foreach ($pedidos as $pedido) {
            PedidoEstado::create([
                'pedido_id'    => $pedido->id,
                'proyecto_id'  => $pedido->proyecto_id,
                'usuario_id'   => Auth::id(),
                'estado'       => 'CANCELADA',
                'fecha_inicio' => now(),
            ]);
        }

        $this->dispatch('muestraActualizada')
             ->to(\App\Livewire\Produccion\Muestras\AdminMuestrasTabs::class);
    }

    /** DATA + filtros */
    public function getPedidosProperty()
    {
        $q = Pedido::deMuestra()
            ->estatusMuestra('CANCELADA')
            ->with([
                'producto.categoria',
                'archivo',
                'cliente',
                'estados.usuario',
            ])
            ->latest('id');

        // ID / Proyecto-ID
        if ($this->f_id !== '') {
            $t = trim($this->f_id);
            $q->where(function ($qq) use ($t) {
                $qq->where('id', $t)
                   ->orWhere('proyecto_id', $t)
                   ->orWhereRaw("CONCAT(proyecto_id,'-',id) LIKE ?", ["%{$t}%"]);
            });
        }

        // Producto / Categoría
        if ($this->f_producto !== '') {
            $t = "%{$this->f_producto}%";
            $q->where(function ($qq) use ($t) {
                $qq->whereHas('producto', fn($z) => $z->where('nombre', 'like', $t))
                   ->orWhereHas('producto.categoria', fn($z) => $z->where('nombre', 'like', $t));
            });
        }

        // Cliente
        if ($this->f_cliente !== '') {
            $t = "%{$this->f_cliente}%";
            $q->whereHas('cliente', function ($z) use ($t) {
                $z->where('nombre', 'like', $t)
                  ->orWhere('razon_social', 'like', $t);
            });
        }

        // Archivo / Versión
        if ($this->f_archivo !== '') {
            $t = "%{$this->f_archivo}%";
            $q->whereHas('archivo', function ($z) use ($t) {
                $z->where('nombre_archivo', 'like', $t)
                  ->orWhere('version', 'like', $t);
            });
        }

        // Piezas (>=)
        if ($this->f_total_min !== '' && is_numeric($this->f_total_min)) {
            $q->where('total', '>=', (float) $this->f_total_min);
        }

        // Solicitó (usuario del último estado $estadoColumna)
        if ($this->f_usuario !== '') {
            $t = "%{$this->f_usuario}%";
            $col = $this->estadoColumna;
            $q->whereHas('estados', function ($z) use ($t, $col) {
                $z->where('estado', $col)
                  ->whereHas('usuario', fn($u) => $u->where('name', 'like', $t));
            });
        }

        // Instrucciones
        if ($this->f_instrucciones !== '') {
            $t = "%{$this->f_instrucciones}%";
            $q->where('instrucciones_muestra', 'like', $t);
        }

        // Estatus extra (si se quisiera ver otro distinto a CANCELADA)
        if ($this->f_estatus !== '') {
            $q->whereRaw('UPPER(estatus_muestra) = ?', [mb_strtoupper($this->f_estatus)]);
        }

        return $q->paginate(10);
    }

    public bool $modalEstadosOpen = false;
    public ?int $pedidoEstadosId = null;
    public array $estadosModal = [];

    public function abrirModalEstados(int $pedidoId): void
    {
        $pedido = Pedido::with(['estados' => fn($q) => $q->with('usuario')->orderByDesc('id')])
            ->findOrFail($pedidoId);

        $this->pedidoEstadosId = $pedidoId;

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

    private function ultimosPorEstado($pedidos, string $estado)
    {
        $ids = $pedidos->pluck('id');

        return PedidoEstado::with('usuario')
            ->whereIn('pedido_id', $ids)
            ->where('estado', $estado)
            ->orderByDesc('created_at')
            ->get()
            ->unique('pedido_id')
            ->keyBy('pedido_id');
    }

    /** Acción: restaurar cancelación -> PENDIENTE */
    public function restaurarPendiente(array $ids = []): void
    {
        $ids = $ids ?: $this->selected;
        if (empty($ids)) return;

        // 1) Actualiza estatus de la muestra
        Pedido::deMuestra()
            ->whereIn('id', $ids)
            ->update(['estatus_muestra' => 'PENDIENTE']);

        // 2) Registra estado y cierra el último CANCELADA si aplica
        $pedidos = Pedido::query()
            ->select('id', 'proyecto_id')
            ->whereIn('id', $ids)
            ->get();

        foreach ($pedidos as $pedido) {
            // Cierra el último estado CANCELADA (si no tenía fecha_fin)
            $ultimoCancel = PedidoEstado::where('pedido_id', $pedido->id)
                ->where('estado', 'CANCELADA')
                ->latest('id')
                ->first();

            if ($ultimoCancel && is_null($ultimoCancel->fecha_fin)) {
                $ultimoCancel->update(['fecha_fin' => now()]);
            }

            // Crea el nuevo estado PENDIENTE
            PedidoEstado::create([
                'pedido_id'    => $pedido->id,
                'proyecto_id'  => $pedido->proyecto_id,
                'usuario_id'   => Auth::id(),
                'estado'       => 'PENDIENTE',
                'fecha_inicio' => now(),
            ]);
        }

        $this->reset('selected');

        // Notifica al padre para refrescar contadores (v3 -> dispatch)
        $this->dispatch('muestraActualizada')
            ->to(\App\Livewire\Produccion\Muestras\AdminMuestrasTabs::class);
    }

    public function render()
    {
        $pedidos = $this->pedidos;
        $ultimosPorEstado = $this->ultimosPorEstado($pedidos, $this->estadoColumna);

        return view('livewire.produccion.muestras.tab-cancelada', [
            'pedidos'          => $pedidos,
            'ultimosPorEstado' => $ultimosPorEstado,
        ]);
    }
}
