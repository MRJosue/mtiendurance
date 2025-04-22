<?php

namespace App\Livewire\Reprogramacion;


use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Proyecto;
use App\Models\DireccionEntrega;
use App\Models\DireccionFiscal;
use App\Models\Ciudad;
use App\Models\Pais;
use App\Models\Estado;
use App\Models\TipoEnvio;

use App\Models\Pedido;
use App\Models\PedidoEstado;
use App\Models\PedidoTalla;

use App\Models\PedidoCaracteristica;
use App\Models\PedidoOpcion;

use App\Models\ArchivoProyecto;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Caracteristica;
use App\Models\Talla;
use App\Models\Opcion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class EditProject extends Component
{
    use WithFileUploads;

    public $ProyectoId;


    public $direccion_fiscal;
    public $direccion_entrega;

    public $existingFiles = [];

    public $nombre;
    public $descripcion;
    public $fecha_produccion;
    public $fecha_embarque;
    public $fecha_entrega;

    // Variables para archivos
    public $files = [];
    public $fileDescriptions = [];
    public $uploadedFiles = [];

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


    public $seleccion_armado = null;
    public $mostrar_selector_armado = false;

    public $producto_flag_armado;

    public function mount($ProyectoId)
    {
        $this->ProyectoId = $ProyectoId;
        

        $preProyecto = Proyecto::findOrFail($ProyectoId);

        $this->nombre = $preProyecto->nombre;
        $this->descripcion = $preProyecto->descripcion;

       
        $this->fecha_produccion = Carbon::parse($preProyecto->fecha_produccion)->format('Y-m-d');
         Log::info('Este es un mensaje de información.'. $this->fecha_produccion);  

        $this->fecha_embarque = Carbon::parse($preProyecto->fecha_embarque)->format('Y-m-d');
         Log::info('Este es un mensaje de información.'. $this->fecha_embarque);  

        $this->fecha_entrega = Carbon::parse($preProyecto->fecha_entrega)->format('Y-m-d');
         Log::info('Este es un mensaje de información.'.$this->fecha_entrega);  
         

        $this->categoria_id = json_decode($preProyecto->categoria_sel)->id;
        $this->producto_id = json_decode($preProyecto->producto_sel)->id;
        $this->total_piezas = json_decode($preProyecto->total_piezas_sel)->total ?? 0;
        $this->direccion_fiscal = $preProyecto->direccion_fiscal;
        $this->direccion_fiscal_id = $preProyecto->direccion_fiscal_id;
        $this->direccion_entrega = $preProyecto->direccion_entrega;
        $this->direccion_entrega_id = $preProyecto->direccion_entrega_id;


        // cargamos los tipos de envio
        $this->cargarTiposEnvio();

        $this->id_tipo_envio = $preProyecto->id_tipo_envio;

        // Cargar productos de la categoría seleccionada
        $this->productos = Producto::where('categoria_id', $this->categoria_id)->get();

        $this->producto_flag_armado = Producto::where('id', $this->producto_id)->value('flag_armado');
        Log::debug('producto_flag_armado', ['data' => $this->producto_flag_armado]);

        // Evaluamos si el producto seleccionado puede mostrar la pregunta de armado 
        
        // si si asignamos el valor del imput
        if ( $this->producto_flag_armado == 1) {
            Log::debug('if', ['data' => $this->producto_flag_armado]);
            $this->mostrar_selector_armado = true;
            // asignamos el valor del armado del select 
            $this->seleccion_armado =  $preProyecto->flag_armado;
        } else {

            Log::debug('Else ', ['data' => $this->producto_flag_armado]);
            $this->mostrar_selector_armado = false;
        }

        // Carga lascategorias
        $this -> despligaformopciones();


        // // Carga lascategorias
        // $this -> onProductoChange();


        // Selecciona las opciones seleccionadas
        $this->enviarOpcionesSeleccionadas();



        // Cargar tallas si es "Playeras"
        $categoria = Categoria::find($this->categoria_id);
        $this->mostrarFormularioTallas = $categoria && strtolower($categoria->nombre) === 'playeras';
        $this->tallas = Talla::all();
        
        $this->tallasSeleccionadas = json_decode($preProyecto->total_piezas_sel, true)['detalle_tallas'] ?? [];


        // Log::debug('Valor de mostrar_selector_armado:', [$this->mostrar_selector_armado]);

        // $this->mostrar_selector_armado = $preProyecto->flag_armado;
    }

    public function update()
    {

        Log::debug('Pre validaciones');
        $this->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_entrega' => 'nullable|date',
            'categoria_id' => 'required|exists:categorias,id',
            'producto_id' => 'required|exists:productos,id',
        ]);

        $this->total_piezas = 0;

         $totalPiezasFinal = $this->mostrarFormularioTallas ? array_sum($this->tallasSeleccionadas) : $this->total_piezas;
         $totalPiezasFinal = $this->mostrarFormularioTallas ? array_sum((array) $this->tallasSeleccionadas) : $this->total_piezas;
        // Actualizar el preproyecto
        $preProyecto = Proyecto::findOrFail($this->ProyectoId);
        $preProyecto->update([
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            
            'categoria_sel' => json_encode(['id' => $this->categoria_id, 'nombre' => Categoria::find($this->categoria_id)->nombre]),
            'producto_sel' => json_encode(['id' => $this->producto_id, 'nombre' => Producto::find($this->producto_id)->nombre]),
            'caracteristicas_sel' => json_encode($this->caracteristicas_sel),
            'opciones_sel' => json_encode($this->opciones_sel),

            'total_piezas_sel' => json_encode([
                'total' => $totalPiezasFinal,
                'detalle_tallas' => $this->mostrarFormularioTallas ? $this->tallasSeleccionadas : null
            ]),

        ]);


        // Actualizar pedidos relacionados
        $pedidos = Pedido::where('proyecto_id', $this->ProyectoId)
            ->whereIn('estado', ['POR APROBAR', 'POR REPROGRAMAR'])
            ->get();

        foreach ($pedidos as $pedido) {
            $pedido->update([
                'producto_id' => $this->producto_id,
                'total' => $totalPiezasFinal,
                'estado'=>'POR APROBAR'
            ]);

            PedidoCaracteristica::where('pedido_id', $pedido->id)->delete();
            PedidoOpcion::where('pedido_id', $pedido->id)->delete();
            PedidoTalla::where('pedido_id', $pedido->id)->delete();

            foreach ($this->caracteristicas_sel as $caracteristica) {
                PedidoCaracteristica::create([
                    'pedido_id' => $pedido->id,
                    'caracteristica_id' => $caracteristica['id'],
                ]);

                if (!empty($caracteristica['opciones'])) {
                    foreach ($caracteristica['opciones'] as $opcion) {
                        PedidoOpcion::create([
                            'pedido_id' => $pedido->id,
                            'opcion_id' => $opcion['id'],
                            'valor' => $opcion['valoru'] ?? null,
                        ]);
                    }
                }
            }

            if ($this->mostrarFormularioTallas && isset($this->tallasSeleccionadas)) {
                foreach ($this->tallasSeleccionadas as $grupoId => $tallas) {
                    foreach ($tallas as $tallaId => $cantidad) {
                        PedidoTalla::create([
                            'pedido_id' => $pedido->id,
                            'talla_id' => $tallaId,
                            'grupo_talla_id' => $grupoId,
                            'cantidad' => 0, // se inicializa en cero
                        ]);
                    }
                }
            }
            PedidoEstado::create([
                'pedido_id' => $pedido->id,
                'proyecto_id' => $this->ProyectoId,
                'usuario_id' => auth()->id(),
                'estado' => 'Se reprograma El pedido',
                'fecha_inicio' => now(),
                'fecha_fin' => null,
            ]);

        }


        // Insertamos al log de pedidos

        

        session()->flash('message', 'Preproyecto actualizado exitosamente.');
        return redirect()->route('preproyectos.index');
    }
    
   // Funciones

    public function render()
    {




        return view('livewire.reprogramacion.edit-project', [

            'categorias' => Categoria::all(),
            'productos' => $this->productos,
            'tiposEnvio' => $this->tipos_envio,
            'mostrarFormularioTallas'=> $this->mostrarFormularioTallas,
            'mostrar_selector_armado' => $this->mostrar_selector_armado,
              'mostrarFormularioTallas'=> $this->mostrarFormularioTallas,
            'direccionesFiscales' => DireccionFiscal::where('usuario_id', Auth::id())->get(),
            'direccionesEntrega' => DireccionEntrega::where('usuario_id', Auth::id())->get(),
        ]);
    }

    public function onCategoriaChange()
    {

        $this->producto_id = null;
        $this->productos = Producto::where('categoria_id', $this->categoria_id)->get();

    }

    public function despliega_form_tallas()
    {
        // Verifica si la categoría seleccionada requiere tallas
        $categoria = Categoria::find($this->categoria_id);
        $this->mostrarFormularioTallas = $categoria && $categoria->flag_tallas == 1;

        // Reinicializar las tallas y las cantidades seleccionadas
        $this->tallas = collect(); // Vaciar antes de asignar nuevas tallas
        $this->tallasSeleccionadas = [];

        if ($this->mostrarFormularioTallas && $this->producto_id) {
            // Cargar las tallas asociadas al producto mediante los grupos de tallas
            $this->tallas = Talla::with('gruposTallas')
            ->whereHas('gruposTallas.productos', function ($query) {
                $query->where('producto_id', $this->producto_id);
            })
            ->get();

            // Inicializar el array de selección con valores en 0
            foreach ($this->tallas as $talla) {
                $this->tallasSeleccionadas[$talla->id] = 0;
            }
        }
    }

    public function onProductoChange()
            {

                $producto = Producto::find($this->producto_id);

                if ($producto && $producto->flag_armado == 1) {
                    $this->mostrar_selector_armado = true;
                } else {
                    $this->mostrar_selector_armado = false;

                    $this->despligaformopciones();
                }
            
                $this->seleccion_armado = null;
            

        }


    public function despligaformopciones(){

            $this->despliega_form_tallas(); // Obtiene las tallas según el nuevo producto

            // Asegurar que el producto ha sido seleccionado
            if (!$this->producto_id) {
                $this->tallas = collect(); // Vaciar las tallas si no hay producto seleccionado
                return;
            }

            // Obtener todas las tallas asociadas al producto a través de los grupos de tallas
            $this->tallas = Talla::with('gruposTallas')
                ->whereHas('gruposTallas.productos', function ($query) {
                    $query->where('producto_id', $this->producto_id);
                })
                ->get();

        

            // Reiniciar la selección de tallas
            $this->tallasSeleccionadas = [];

            foreach ($this->tallas as $talla) {
                foreach ($talla->gruposTallas as $grupo) {
                    $this->tallasSeleccionadas[$grupo->id][$talla->id] = 0;
                }
            }

            Log::debug('Estructura de tallas seleccionadas después de reset:', ['data' => $this->tallasSeleccionadas]);

            // Limpiar opciones previas de características
            $this->caracteristica_id = null;

            $caracteristicasQuery = Caracteristica::where('ind_activo', 1)
                ->whereHas('productos', function ($query) {
                    $query->where('producto_id', $this->producto_id);
                });
            
            // Si hay selector de armado, filtrar por flag_armado
            if ($this->mostrar_selector_armado && $this->seleccion_armado !== null) {
                $caracteristicasQuery->whereHas('productos', function ($q) {
                    $q->where('producto_id', $this->producto_id)
                      ->where('producto_caracteristica.flag_armado', $this->seleccion_armado);
                });
            }
            
            $this->caracteristicas_sel = $caracteristicasQuery
                ->get()
                ->map(function ($caracteristica) {
                    $opciones = Opcion::where('ind_activo', 1)
                        ->whereHas('caracteristicas', function ($query) use ($caracteristica) {
                            $query->where('caracteristica_id', $caracteristica->id);
                        })
                        ->get();
            
                    $opcionesArray = $opciones->map(function ($opcion) {
                        return [
                            'id' => $opcion->id,
                            'nombre' => $opcion->nombre,
                            'valoru' => $opcion->valoru,
                        ];
                    })->toArray();
            
                    return [
                        'id' => $caracteristica->id,
                        'nombre' => $caracteristica->nombre,
                        'flag_seleccion_multiple' => $caracteristica->flag_seleccion_multiple,
                        'opciones' => count($opcionesArray) === 1 ? $opcionesArray : [],
                    ];
                })
                ->toArray();
            

            $this->opciones_sel = [];

            foreach ($this->caracteristicas_sel as $caracteristica) {
                if (isset($caracteristica['opciones']) && count($caracteristica['opciones']) === 1) {
                    $this->opciones_sel[$caracteristica['id']] = $caracteristica['opciones'][0];
                }
            }


    }


    // public function onProductoChange(){
    //                 $this->despliega_form_tallas(); // Obtiene las tallas según el nuevo producto

    //                 // Asegurar que el producto ha sido seleccionado
    //                 if (!$this->producto_id) {
    //                     $this->tallas = collect(); // Vaciar las tallas si no hay producto seleccionado
    //                     return;
    //                 }

    //                 // Obtener todas las tallas asociadas al producto a través de los grupos de tallas
    //                 $this->tallas = Talla::with('gruposTallas')
    //                 ->whereHas('gruposTallas.productos', function ($query) {
    //                     $query->where('producto_id', $this->producto_id);
    //                 })
    //                 ->get();


    //                 // Reiniciar la selección de tallas
    //                 $this->tallasSeleccionadas = [];

    //                 foreach ($this->tallas as $talla) {
    //                     foreach ($talla->gruposTallas as $grupo) {
    //                         $this->tallasSeleccionadas[$grupo->id][$talla->id] = 0;
    //                     }
    //                 }

    //                 Log::debug('Estructura de tallas seleccionadas después de reset:', ['data' => $this->tallasSeleccionadas]);

    //                 // Limpiar opciones previas de características
    //             // Limpiar opciones previas de características
    //             $this->caracteristica_id = null;
    //             $this->caracteristicas_sel = Caracteristica::where('ind_activo', 1)
    //             ->whereHas('productos', function ($query) {
    //                 $query->where('producto_id', $this->producto_id);
    //             })
    //             ->get()
    //             ->map(function ($caracteristica) {
    //                 $opciones = Opcion::where('ind_activo', 1)
    //                     ->whereHas('caracteristicas', function ($query) use ($caracteristica) {
    //                         $query->where('caracteristica_id', $caracteristica->id);
    //                     })
    //                     ->get();
            
    //                 $opcionesArray = $opciones->map(function ($opcion) {
    //                     return [
    //                         'id' => $opcion->id,
    //                         'nombre' => $opcion->nombre,
    //                         'valoru' => $opcion->valoru,
    //                     ];
    //                 })->toArray();
            
    //                 return [
    //                     'id' => $caracteristica->id,
    //                     'nombre' => $caracteristica->nombre,
    //                     'flag_seleccion_multiple' => $caracteristica->flag_seleccion_multiple,
    //                     'opciones' => count($opcionesArray) === 1 ? $opcionesArray : [],
    //                 ];
    //             })
    //             ->toArray();

    //             $this->opciones_sel = [];

    //                 foreach ($this->caracteristicas_sel as $caracteristica) {
    //                     if (isset($caracteristica['opciones']) && count($caracteristica['opciones']) === 1) {
    //                         $this->opciones_sel[$caracteristica['id']] = $caracteristica['opciones'][0];
    //                     }
    //                 }


    // }

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
                    $caracteristica['opciones'] = [
                        [
                            'id' => $opcion->id,
                            'nombre' => $opcion->nombre,
                            'valoru' => $opcion->valoru,
                        ]
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

    public function actualizarTalla($grupoId, $tallaId, $cantidad)
    {
        $cantidad = intval($cantidad); // Asegurar que el valor sea un entero
    
        // Si el grupo no existe, inicializarlo como array
        if (!isset($this->tallasSeleccionadas[$grupoId]) || !is_array($this->tallasSeleccionadas[$grupoId])) {
            $this->tallasSeleccionadas[$grupoId] = [];
        }
    
        // Si la talla ya existe, actualizar el valor
        if (isset($this->tallasSeleccionadas[$grupoId][$tallaId])) {
            $this->tallasSeleccionadas[$grupoId][$tallaId] = $cantidad;
        } else {
            // Si la talla no existe, agregarla al arreglo
            $this->tallasSeleccionadas[$grupoId][$tallaId] = $cantidad;
        }
    
        Log::debug("Tallas seleccionadas actualizadas:", ['data' => $this->tallasSeleccionadas]);
    }
    

    public function enviarOpcionesSeleccionadas()
    {
        $preProyecto = Proyecto::findOrFail($this->ProyectoId);
        $this->asignarOpcionesSeleccionadas($preProyecto->caracteristicas_sel);
    }

    public function asignarOpcionesSeleccionadas($opcionesSeleccionadas)
    {

            // Verifica si la variable es un string antes de decodificar
            if (is_array($opcionesSeleccionadas)) {
                $opciones = $opcionesSeleccionadas; // Ya es un array, no decodificar
            } elseif (is_string($opcionesSeleccionadas)) {
                $opciones = json_decode($opcionesSeleccionadas, true);
            } else {
                $opciones = [];
            }

            if (!$opciones) {
                return;
            }

            foreach ($this->caracteristicas_sel as &$caracteristica) {
                $opcionEncontrada = collect($opciones)->firstWhere('id', $caracteristica['id']);
                
                if ($opcionEncontrada) {
                    $caracteristica['opciones'] = $opcionEncontrada['opciones'] ?? [];
                }
            }

            // Guardar en la base de datos el nuevo JSON
            Proyecto::where('id', $this->ProyectoId)->update([
                'caracteristicas_sel' => json_encode($this->caracteristicas_sel)
            ]);



    }
    

    public function setReadOnlyMode()
    {
        $this->dispatch('setReadOnlyMode');
    }
}



//return view('livewire.preproyectos.edit-pre-project');

//  EditProject     return view('livewire.reprogramacion.edit-project');