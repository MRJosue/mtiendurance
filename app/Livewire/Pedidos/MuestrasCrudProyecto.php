<?php

namespace App\Livewire\Pedidos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Pedido;
use App\Models\Proyecto;
use App\Models\Producto;
use App\Models\DireccionEntrega;
use App\Models\DireccionFiscal;
use App\Models\TipoEnvio;
use App\Models\PedidoTalla;
use App\Models\GrupoTalla;
use App\Models\ProductoGrupoTalla;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MuestrasCrudProyecto extends Component
{
    use WithPagination;

    public $proyectoId;

    // Filtros (todos los campos de la tabla)
    public $f_id = '';
    public $f_producto = '';
    public $f_cliente = '';
    public $f_archivo = '';
    public $f_total_min = '';
    public $f_usuario = '';
    public $f_instrucciones = '';
    public $f_estatus = '';

    // Mantener filtros en la URL
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
    ];

    // ----- resto de props de tu componente (modal, fechas, etc.) -----
    public $modal = false;
    public $pedidoId, $total, $estatus, $tipo, $estado, $fecha_produccion, $fecha_embarque, $fecha_entrega;
    public $direccion_fiscal_id, $direccion_fiscal, $direccion_entrega_id, $direccion_entrega, $id_tipo_envio;
    public $nombre_tipo_envio, $tipos_envio = [];
    public $mensaje_produccion;
    public $tallas_disponibles = [];
    public $cantidades_tallas = [];
    public $mostrar_total = true;
    public $producto_id;

    protected $listeners = ['ActualizarTablaMuestra' => 'actualizarTabla'];

        // 👇 NUEVO: filtro para activos / inactivos
    public array $filters = [
        'inactivos' => false, // false => solo activos, true => solo inactivos
    ];



    // Reset de página al cambiar cualquier filtro
    public function updated($prop)
    {
        if (
            str_starts_with($prop, 'f_') ||
            str_starts_with($prop, 'filters.')
        ) {
            $this->resetPage();
        }
    }

    protected function rules()
    {
        return [
            'total' => [function ($attribute, $value, $fail) {
                if (empty(array_filter($this->cantidades_tallas)) && empty($value)) {
                    $fail('Debe ingresar un total o capturar cantidades por tallas.');
                }
            }],
            'estatus' => 'required|string',
            'tipo'    => 'required|in:PEDIDO,MUESTRA',
            'estado'  => 'required|in:POR PROGRAMAR,PROGRAMADO,IMPRESIÓN,PRODUCCIÓN,COSTURA,ENTREGA,FACTURACIÓN,COMPLETADO,RECHAZADO',
            'fecha_produccion' => 'nullable|date',
            'fecha_embarque'   => 'nullable|date',
            'fecha_entrega'    => 'nullable|date',
        ];
    }

    // ... (tus métodos abrirModal, guardar, cargarTallas, updatedCantidadesTallas, cargarTiposEnvio, on_Calcula_Fechas_Entrega, validarFechaEntrega se quedan tal cual) ...

    public function render()
    {
        $proyecto = Proyecto::find($this->proyectoId);

        $query = Pedido::query()
            ->where('proyecto_id', $this->proyectoId)
            ->where('tipo', 'MUESTRA')
            ->with([
                'archivo',
                'usuario',
                'cliente',               // asumiendo relación cliente()
                'producto.categoria',    // para producto y categoría
                'archivos' => fn($q) => $q
                ->where('tipo_carga', 3)
                ->orderByDesc('id')
                ->limit(1),
            ]);


        // 👇 Filtro base: activos / inactivos
        if ($this->filters['inactivos'] ?? false) {
            // check marcado → solo inactivos
            $query->where('ind_activo', 0);
        } else {
            // sin check → solo activos
            $query->where('ind_activo', 1);
        }
        

        // Filtros
        if ($this->f_id !== '') {
            // Permite buscar por "proyecto-id" o por el id individual
            $texto = trim($this->f_id);
            $query->where(function ($q) use ($texto) {
                $q->where('id', $texto)
                  ->orWhere('proyecto_id', $texto)
                  ->orWhereRaw("CONCAT(proyecto_id,'-',id) like ?", ["%{$texto}%"]);
            });
        }

        if ($this->f_producto !== '') {
            $t = "%{$this->f_producto}%";
            $query->where(function ($q) use ($t) {
                $q->whereHas('producto', fn($qq) => $qq->where('nombre', 'like', $t))
                  ->orWhereHas('producto.categoria', fn($qq) => $qq->where('nombre', 'like', $t));
            });
        }

        if ($this->f_cliente !== '') {
            $t = "%{$this->f_cliente}%";
            // por relación cliente() en Pedido
            $query->whereHas('cliente', function ($qq) use ($t) {
                $qq->where('nombre', 'like', $t)
                   ->orWhere('razon_social', 'like', $t);
            });
        }

        if ($this->f_archivo !== '') {
            $t = "%{$this->f_archivo}%";
            $query->whereHas('archivo', function ($qq) use ($t) {
                $qq->where('nombre_archivo', 'like', $t)
                   ->orWhere('version', 'like', $t);
            });
        }

        if ($this->f_total_min !== '') {
            if (is_numeric($this->f_total_min)) {
                $query->where('total', '>=', (float)$this->f_total_min);
            }
        }

        if ($this->f_usuario !== '') {
            $t = "%{$this->f_usuario}%";
            $query->whereHas('usuario', fn($qq) => $qq->where('name', 'like', $t));
        }

        if ($this->f_instrucciones !== '') {
            $t = "%{$this->f_instrucciones}%";
            $query->where('instrucciones_muestra', 'like', $t);
        }

        if ($this->f_estatus !== '') {
            $query->whereRaw('UPPER(estatus_muestra) = ?', [mb_strtoupper($this->f_estatus)]);
        }

        return view('livewire.pedidos.muestras-crud-proyecto', [
            'tiposEnvio' => TipoEnvio::all(),
            'direccionesFiscales' => DireccionFiscal::where('usuario_id', $proyecto->usuario_id)->get(),
            'direccionesEntrega'  => DireccionEntrega::where('usuario_id', $proyecto->usuario_id)->get(),
            'pedidos' => $query->orderByDesc('id')->paginate(10),
            'esCliente' => Auth::user()?->hasRole('cliente') ?? false,
        ]);
    }



    // Nueva propiedad para controlar la sección de filtros
    public $mostrarFiltros = true;

    // Botón "Filtrar" (opcional: ya estás filtrando con wire:model.live)
    // Aquí solo reseteamos la página para asegurar resultados desde página 1
    public function buscarPorFiltros()
    {
        $this->resetPage();
    }

    public function actualizarTabla()
    {
        $this->resetPage();
    }
}
