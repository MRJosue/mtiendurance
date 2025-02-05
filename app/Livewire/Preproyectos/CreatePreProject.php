<?php

namespace App\Livewire\Preproyectos;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\PreProyecto;
use App\Models\DireccionEntrega;
use App\Models\DireccionFiscal;
use App\Models\Ciudad;
use App\Models\TipoEnvio;

use App\Models\ArchivoProyecto;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Caracteristica;
use App\Models\Talla;
use App\Models\Opcion;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;



use Illuminate\Support\Facades\Log;

class CreatePreProject extends Component
{
    use WithFileUploads;

    public $nombre;
    public $descripcion;
    public $fecha_produccion;
    public $fecha_embarque;
    public $fecha_entrega;
    public $files = [];
    public $fileDescriptions = [];

    public $categoria_id;
    public $producto_id;
    public $productos = [];
    public $caracteristicas_sel = [];
    public $opciones_sel = [];

    public $total_piezas;
    public $tallas = [];
    public $tallasSeleccionadas = [];
    public $mostrarFormularioTallas = false;

    public $caracteristica_id;
    public $direccion_fiscal_id;


    public $id_tipo_envio;
    public $direccion_entrega_id;
    public $tipos_envio = [];

    public $mensaje_produccion;


    

    public function onCategoriaChange()
    {
        // $this->producto_id = null;
        // $this->productos = Producto::whereHas('categorias', function ($query) {
        //     $query->where('categoria_id', $this->categoria_id);
        // })->get();

        // //Ejecutamos el metodo para cargar cantidades
        // $this->on_muestra_tallas();

        // $this->caracteristicas_sel = [];
        // $this->opciones_sel = [];

        $this->producto_id = null;
        $this->productos = Producto::whereHas('categorias', function ($query) {
            $query->where('categoria_id', $this->categoria_id);
        })->get();

        // Verifica si la categoría seleccionada es "Playeras"
        $categoria = Categoria::find($this->categoria_id);
        $this->mostrarFormularioTallas = $categoria && strtolower($categoria->nombre) === 'playeras';

        // Reset valores de tallas
        if (!$this->mostrarFormularioTallas) {
            $this->tallasSeleccionadas = [];
        }
    }

    public function onProductoChange()
    {
        $this->caracteristica_id = null;
        $this->caracteristicas_sel = Caracteristica::whereHas('productos', function ($query) {
            $query->where('producto_id', $this->producto_id);
        })->get()->map(function ($caracteristica) {
            return [
                'id' => $caracteristica->id,
                'nombre' => $caracteristica->nombre,
                'flag_seleccion_multiple' => $caracteristica->flag_seleccion_multiple,
                'opciones' => []
            ];
        })->toArray();

        $this->opciones_sel = [];

        $this->on_Calcula_Fechas_Entrega();
    }

    public function addOpcion($caracteristicaIndex, $opcion_id)
    {
        if (!isset($this->caracteristicas_sel[$caracteristicaIndex])) {
            return;
        }

        $caracteristica = &$this->caracteristicas_sel[$caracteristicaIndex];
        $opcion = Opcion::find($opcion_id);

        if ($opcion) {
            if (!$caracteristica['flag_seleccion_multiple']) {
                // Si la característica no permite selección múltiple, solo se puede elegir una opción
                $caracteristica['opciones'] = [
                    [
                        'id' => $opcion->id,
                        'nombre' => $opcion->nombre,
                        'valoru' => $opcion->valoru,
                    ]
                ];
            } else {
                // Si permite selección múltiple, se pueden agregar más opciones
                if (!in_array($opcion->id, array_column($caracteristica['opciones'], 'id'))) {
                    $caracteristica['opciones'][] = [
                        'id' => $opcion->id,
                        'nombre' => $opcion->nombre,
                        'valoru' => $opcion->valoru,
                    ];
                }
            }
        }
    }

    public function removeOpcion($caracteristicaIndex, $opcionIndex)
    {
        unset($this->caracteristicas_sel[$caracteristicaIndex]['opciones'][$opcionIndex]);
        $this->caracteristicas_sel[$caracteristicaIndex]['opciones'] = array_values($this->caracteristicas_sel[$caracteristicaIndex]['opciones']);
    }


    public function updatedDireccionEntregaId()
    {
        $this->cargarTiposEnvio();
    }

    public function cargarTiposEnvio()
    {
        if ($this->direccion_entrega_id) {
            $direccion = DireccionEntrega::find($this->direccion_entrega_id);
            if ($direccion && $direccion->ciudad) {
                $this->tipos_envio = $direccion->ciudad->tipoEnvios()->get();
            } else {
                $this->tipos_envio = [];
            }
        } else {
            $this->tipos_envio = [];
        }
    }


    public function create()
    {


        $this->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_produccion' => 'nullable|date',
            'fecha_embarque' => 'nullable|date',
            'fecha_entrega' => 'nullable|date',
            'categoria_id' => 'required|exists:categorias,id',
            'producto_id' => 'required|exists:productos,id',
            'direccion_fiscal_id' => 'required|exists:direcciones_entrega,id',
            'direccion_entrega_id' => 'required|exists:direcciones_entrega,id',
            'id_tipo_envio'=> 'required',
            'total_piezas' => $this->mostrarFormularioTallas ? 'nullable' : 'required|integer|min:1',
            'tallasSeleccionadas' => $this->mostrarFormularioTallas ? 'required|array|min:1' : 'nullable',
        ]);

        $totalPiezasFinal = $this->mostrarFormularioTallas ? array_sum($this->tallasSeleccionadas) : $this->total_piezas;

        //antes de crear asignamos fechas
        $this->on_Calcula_Fechas_Entrega();

        // Asignamos copnjunto direccion entrega

        $Auxiliar_direccion_entrega = '';


        $preProyecto = PreProyecto::create([
            'usuario_id' => Auth::id(),
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'tipo' => 'PROYECTO',
            'numero_muestras' => 0,
            'estado' => 'PENDIENTE',
            'fecha_produccion' => $this->fecha_produccion,
            'fecha_embarque' => $this->fecha_embarque,
            'fecha_entrega' => $this->fecha_entrega,
            'categoria_sel' => json_encode(['id' => $this->categoria_id, 'nombre' => Categoria::find($this->categoria_id)->nombre]),
            'producto_sel' => json_encode(['id' => $this->producto_id, 'nombre' => Producto::find($this->producto_id)->nombre]),
            'caracteristicas_sel' => json_encode($this->caracteristicas_sel),
            'opciones_sel' => json_encode($this->opciones_sel),
            'direccion_entrega'=> $Auxiliar_direccion_entrega,
            'id_tipo_envio' => $this->id_tipo_envio,
            'total_piezas_sel' => json_encode([
                'total' => $totalPiezasFinal,
                'detalle_tallas' => $this->mostrarFormularioTallas ? $this->tallasSeleccionadas : null
            ]),
        ]);

        session()->flash('message', 'Preproyecto creado exitosamente.');
        return redirect()->route('preproyectos.index');
    }


    public function on_Calcula_Fechas_Entrega()
    {


     if ( $this->fecha_entrega) {
               // Registrar la fecha ingresada
               Log::debug('Fecha de entrega ingresada', ['fecha_entrega' => $this->fecha_entrega]);
    
               // Convertir la fecha ingresada a un objeto Carbon
               $fecha_entrega = Carbon::parse($this->fecha_entrega);
               $ahora = Carbon::now();
               // Definir los días requeridos
               $dias_produccion_producto = 6;
               $dias_envio = 2;
       
                   // Consultar el tipo de envío seleccionado
                   if (!empty($this->id_tipo_envio)) {
                       $tipoEnvio = TipoEnvio::find($this->id_tipo_envio);
       
                       if ($tipoEnvio) {
                           $dias_envio = $tipoEnvio->dias_envio;
                           Log::debug('Días de envío obtenidos de la BD', ['dias_envio' => $dias_envio]);
                       } else {
                           Log::warning('No se encontró el tipo de envío en la BD', ['id_tipo_envio' => $this->id_tipo_envio]);
                       }
                   }
       
                   // Consultar el producto seleccionado almacenado en dias_produccion
       
                   if (!empty($this->producto_id)) {
                       $tipoEnvio = Producto::find($this->producto_id);
       
                       if ($tipoEnvio) {
                           $dias_produccion_producto = $tipoEnvio->dias_produccion;
                           Log::debug('Días de Producto obtenidos de la BD', ['dias_produccion' => $dias_produccion_producto]);
                       } else {
                           Log::warning('No se encontró el tipo de envío en la BD', ['producto_id' => $this->producto_id]);
                       }
                   }
           
               // Calcular fechas
            
               $fecha_embarque = $fecha_entrega->copy()->subDays($dias_envio);
               $fecha_produccion = $fecha_embarque->copy()->subDays($dias_produccion_producto);
           
               // Guardar las fechas en el formato adecuado para los inputs de tipo "date"
               $this->fecha_produccion = $fecha_produccion->format('Y-m-d'); // Correcto para input date
               $this->fecha_embarque = $fecha_embarque->format('Y-m-d');
       
       
               // Evaluamos si la fecha de produccion esta en tiempo de produccion
       
               if ($fecha_produccion->lt($ahora)) {
                   $this->mensaje_produccion = "⚠️ La fecha de producción calculada ({$this->fecha_produccion}) ya ha pasado. Se requiere una autorización adicional para continuar.";
                   Log::warning('Este proyecto requiere autorización adicional para producción.');
               }else{
                   $this->mensaje_produccion = NULL;
               }
           
               // Log para depuración
               Log::debug('Fechas calculadas', [
                   'fecha_produccion' => $this->fecha_produccion,
                   'fecha_embarque' => $this->fecha_embarque,
               ]);
     }

    }


    public function mount()
    {
        $this->tallas = Talla::all();
    }




    public function render()
    {
        return view('livewire.preproyectos.create-pre-project', [
            'categorias' => Categoria::all(),
            'productos' => $this->productos,
            'tiposEnvio' => $this->tipos_envio,
            'mostrarFormularioTallas'=> $this->mostrarFormularioTallas,
            'direccionesFiscales' => DireccionFiscal::where('usuario_id', Auth::id())->get(),
            'direccionesEntrega' => DireccionEntrega::where('usuario_id', Auth::id())->get(),
        ]);
    }


}
