<?php

namespace App\Livewire\Produccion\Muestras;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use App\Models\Pedido;
use App\Models\PedidoEstado;
use App\Models\ArchivoPedido; // nueva tabla

class TabLista extends Component
{
    use WithPagination, WithFileUploads;

    public array $selected = [];
    public string $estadoColumna = 'SOLICITADA';

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

    /** Modal Estados (existente) */
    public bool $modalEstadosOpen = false;
    public ?int $pedidoEstadosId = null;
    public array $estadosModal = [];

    /** === NUEVO: Modal Entrega === */
    public bool $modalEntregaOpen = false;
    public ?int $entregaPedidoId = null;
    public string $entregaSeleccion = 'PENDIENTE'; // PENDIENTE|DIGITAL|FISICA
    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $evidencia = null;

    /** Validaciones */
    protected function rules(): array
    {
        return [
            'entregaSeleccion' => ['required', Rule::in(['PENDIENTE', 'DIGITAL', 'FISICA'])],
            'evidencia' => [
                function ($attr, $value, $fail) {
                    if (in_array($this->entregaSeleccion, ['DIGITAL', 'FISICA'], true) && !$value) {
                        $fail('La evidencia es obligatoria para entrega DIGITAL o FISICA.');
                    }
                },
                'nullable',
                'file',
                'max:5120', // 5 MB
                'mimetypes:image/jpeg,image/png,image/webp,application/pdf',
            ],
        ];
    }

    /* ================= Filtros/URL ================= */
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
        if (str_starts_with($prop, 'f_')) $this->resetPage();
    }

    public function buscarPorFiltros(): void
    {
        $this->resetPage();
    }

    /* ============== NUEVO: Entrega ============== */

    /** Acción superior: abre modal con la selección (solo 1 pedido permitido) */
    public function abrirModalEntregarSeleccion(): void
    {
        $ids = array_values($this->selected);
        if (count($ids) !== 1) {
            $this->dispatch('notify', type: 'warning', message: 'Selecciona exactamente un pedido.');
            return;
        }
        $this->abrirModalEntregar((int) $ids[0]);
    }

    /** Acción por fila */
    public function abrirModalEntregar(int $pedidoId): void
    {
        $pedido = Pedido::findOrFail($pedidoId);

        if (mb_strtoupper((string) $pedido->estatus_muestra) === 'ENTREGADA') {
            $this->dispatch('notify', type: 'warning', message: 'El pedido ya está ENTREGADA.');
            return;
        }

        $this->resetValidation();
        $this->reset(['evidencia']);
        $this->entregaPedidoId = $pedidoId;
        $this->entregaSeleccion = $pedido->estatus_entrega_muestra ?: 'PENDIENTE';
        $this->modalEntregaOpen = true;
    }

    public function cerrarModalEntregar(): void
    {
        $this->resetValidation();
        $this->reset(['modalEntregaOpen', 'entregaPedidoId', 'entregaSeleccion', 'evidencia']);
    }

    /** Guarda evidencia en archivos_pedido + actualiza estatus + registra PedidoEstado */
    public function confirmarEntrega(): void
    {
        $this->validate();

        $pedido = Pedido::findOrFail($this->entregaPedidoId);

        // 1) Guardar evidencia (opcional)
        $archivoCreado = null;
        if ($this->evidencia) {
            $dir = "pedidos/{$pedido->id}/evidencias";
            $original = $this->evidencia->getClientOriginalName();
            $mime     = $this->evidencia->getMimeType();
            $ext      = $this->evidencia->getClientOriginalExtension() ?: 'bin';
            $nombre   = pathinfo($original, PATHINFO_FILENAME);
            $slug     = Str::slug($nombre) ?: 'evidencia';
            $filename = $slug . '-' . now()->format('YmdHis') . '.' . $ext;

            $path = $this->evidencia->storeAs($dir, $filename, 'public');

            $archivoCreado = ArchivoPedido::create([
                'pedido_id'      => $pedido->id,
                'nombre_archivo' => $original,
                'ruta_archivo'   => $path,
                'tipo_archivo'   => $mime,
                'tipo_carga'     => 3, // evidencia de entrega
                'flag_descarga'  => 1,
                'usuario_id'     => Auth::id(),
                'descripcion'    => 'Evidencia de entrega de muestra',
                'version'        => 0,
            ]);
        }

        // 2) Actualizar pedido (estatus entrega + estatus muestra)
        $pedido->estatus_entrega_muestra = $this->entregaSeleccion; // enum PENDIENTE|DIGITAL|FISICA
        $pedido->estatus_muestra = 'ENTREGADA';
        $pedido->save();

        // 3) Registrar estado
        PedidoEstado::create([
            'pedido_id'    => $pedido->id,
            'proyecto_id'  => $pedido->proyecto_id,
            'usuario_id'   => Auth::id(),
            'estado'       => 'ENTREGADA',
            'fecha_inicio' => now(),
            'fecha_fin'    => now(),
            'comentario'   => $archivoCreado
                                ? 'Entrega confirmada con evidencia: ' . $archivoCreado->nombre_archivo
                                : 'Entrega confirmada',
        ]);

        // 4) Cerrar modal y refrescar
        $this->cerrarModalEntregar();
        $this->dispatch('muestraActualizada')
             ->to(\App\Livewire\Produccion\Muestras\AdminMuestrasTabs::class);
        $this->dispatch('refresh')->self();
    }

    /* ============== Estados (igual que tenías) ============== */

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

    /* ============== Data + filtros ============== */

    public function getPedidosProperty()
    {
        $q = Pedido::deMuestra()
            ->estatusMuestra('MUESTRA LISTA') // esta pestaña es "MUESTRA LISTA"
            ->with(['producto.categoria', 'archivo', 'cliente', 'estados.usuario'])
            ->latest('id');

        if ($this->f_id !== '') {
            $t = trim($this->f_id);
            $q->where(function ($qq) use ($t) {
                $qq->where('id', $t)
                   ->orWhere('proyecto_id', $t)
                   ->orWhereRaw("CONCAT(proyecto_id,'-',id) LIKE ?", ["%{$t}%"]);
            });
        }

        if ($this->f_producto !== '') {
            $t = "%{$this->f_producto}%";
            $q->where(function ($qq) use ($t) {
                $qq->whereHas('producto', fn($z) => $z->where('nombre', 'like', $t))
                   ->orWhereHas('producto.categoria', fn($z) => $z->where('nombre', 'like', $t));
            });
        }

        if ($this->f_cliente !== '') {
            $t = "%{$this->f_cliente}%";
            $q->whereHas('cliente', function ($z) use ($t) {
                $z->where('nombre', 'like', $t)
                  ->orWhere('razon_social', 'like', $t);
            });
        }

        if ($this->f_archivo !== '') {
            $t = "%{$this->f_archivo}%";
            $q->whereHas('archivo', function ($z) use ($t) {
                $z->where('nombre_archivo', 'like', $t)
                  ->orWhere('version', 'like', $t);
            });
        }

        if ($this->f_total_min !== '' && is_numeric($this->f_total_min)) {
            $q->where('total', '>=', (float) $this->f_total_min);
        }

        if ($this->f_usuario !== '') {
            $t = "%{$this->f_usuario}%";
            $q->whereHas('estados', function ($z) use ($t) {
                $z->where('estado', $this->estadoColumna)
                  ->whereHas('usuario', fn($u) => $u->where('name', 'like', $t));
            });
        }

        if ($this->f_instrucciones !== '') {
            $t = "%{$this->f_instrucciones}%";
            $q->where('instrucciones_muestra', 'like', $t);
        }

        if ($this->f_estatus !== '') {
            $q->whereRaw('UPPER(estatus_muestra) = ?', [mb_strtoupper($this->f_estatus)]);
        }

        return $q->paginate(10);
    }

    public function render()
    {
        $pedidos = $this->pedidos;
        $ultimosPorEstado = $this->ultimosPorEstado($pedidos, $this->estadoColumna);

        return view('livewire.produccion.muestras.tab-lista', [
            'pedidos'          => $pedidos,
            'ultimosPorEstado' => $ultimosPorEstado,
        ]);
    }
}
