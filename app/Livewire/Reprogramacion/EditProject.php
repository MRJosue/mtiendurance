<?php

namespace App\Livewire\Reprogramacion;


use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Proyecto;
use App\Models\DireccionEntrega;
use App\Models\DireccionFiscal;
use App\Models\Cliente;

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

use App\Models\User;
use Illuminate\Validation\Rule;



class EditProject extends Component
{
    use WithFileUploads;

    public $ProyectoId;

    public $proyecto;

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

    public string $usuarioQuery = '';
    public ?int $usuario_id_nuevo = null;  // id elegido en el selector
    public array $usuariosSugeridos = [];  // resultados para el dropdown

    public $flag_requiere_proveedor = 0;



public function mount($ProyectoId)
{
    $this->ProyectoId = $ProyectoId;

    $preProyecto = Proyecto::findOrFail($ProyectoId);

    $this->flag_requiere_proveedor = (int) ($preProyecto->flag_requiere_proveedor ?? 0);


    $this->usuario_id_nuevo = Proyecto::where('id', $this->ProyectoId)->value('usuario_id');

    // Fechas seguras
    $this->fecha_produccion = $preProyecto->fecha_produccion
        ? Carbon::parse($preProyecto->fecha_produccion)->format('Y-m-d')
        : null;

    $this->fecha_embarque = $preProyecto->fecha_embarque
        ? Carbon::parse($preProyecto->fecha_embarque)->format('Y-m-d')
        : null;

    $this->fecha_entrega = $preProyecto->fecha_entrega
        ? Carbon::parse($preProyecto->fecha_entrega)->format('Y-m-d')
        : null;

    $this->nombre      = $preProyecto->nombre;
    $this->descripcion = $preProyecto->descripcion;

    // JSONs seguros
    $catSel   = json_decode($preProyecto->categoria_sel ?? '', true) ?: [];
    $prodSel  = json_decode($preProyecto->producto_sel  ?? '', true) ?: [];
    $totSel   = json_decode($preProyecto->total_piezas_sel ?? '', true) ?: [];

    $this->categoria_id   = $catSel['id']   ?? null;
    $this->producto_id    = $prodSel['id']  ?? null;
    $this->total_piezas   = $totSel['total'] ?? 0;

    $this->direccion_fiscal      = $preProyecto->direccion_fiscal;
    $this->direccion_fiscal_id   = $preProyecto->direccion_fiscal_id;
    $this->direccion_entrega     = $preProyecto->direccion_entrega;
    $this->direccion_entrega_id  = $preProyecto->direccion_entrega_id;

    // Tipos de envío (no truena si no hay dirección)
    $this->cargarTiposEnvio();
    $this->id_tipo_envio = $preProyecto->id_tipo_envio;

    // Cargar productos solo si hay categoría válida
    $this->productos = $this->categoria_id
        ? Producto::where('categoria_id', $this->categoria_id)->get()
        : collect();

    // Flag armado del producto (seguro)
    $this->producto_flag_armado = $this->producto_id
        ? (int) Producto::where('id', $this->producto_id)->value('flag_armado')
        : 0;

    // Mostrar selector armado solo si aplica
    $this->mostrar_selector_armado = ($this->producto_flag_armado === 1);
    $this->seleccion_armado        = $this->mostrar_selector_armado ? (int) ($preProyecto->flag_armado ?? 0) : null;

    // Construye características/opciones y tallas según selección actual
    $this->despligaformopciones();

    // Reinyecta opciones previamente guardadas (si existen)
    $this->enviarOpcionesSeleccionadas();

    // Tallas por categoría "playeras" (no truena si no hay categoría)
    $categoria = $this->categoria_id ? Categoria::find($this->categoria_id) : null;
    $this->mostrarFormularioTallas = $categoria && strtolower($categoria->nombre) === 'playeras';
    $this->tallas = Talla::all();

    $this->tallasSeleccionadas = $totSel['detalle_tallas'] ?? [];
}

public function update()
{
    Log::debug('Pre validaciones');

    // Normaliza selects (a veces llegan como "" desde el <select>)
    $this->categoria_id = $this->categoria_id ? (int) $this->categoria_id : null;
    $this->producto_id  = $this->producto_id ? (int) $this->producto_id : null;

    // Validaciones base
    $this->validate([
        'nombre'       => 'required|string|max:255',
        'descripcion'  => 'nullable|string',
        'fecha_entrega'=> 'nullable|date',
        'categoria_id' => 'required|exists:categorias,id',
        'producto_id'  => 'required|exists:productos,id',
    ]);

    // Validación usuario (si viene)
    if (!is_null($this->usuario_id_nuevo)) {
        $this->validate([
            'usuario_id_nuevo' => ['integer', Rule::exists('users', 'id')],
        ]);
    }

    // Total piezas (si hay tallas, el array es anidado: grupo->talla->cantidad)
    $totalPiezasFinal = $this->mostrarFormularioTallas
        ? collect($this->tallasSeleccionadas)->flatten()->sum()
        : (int) ($this->total_piezas ?? 0);

    // Actualizar proyecto
    $preProyecto = Proyecto::findOrFail($this->ProyectoId);

    $updateData = [
        'nombre' => $this->nombre,
        'descripcion' => $this->descripcion,
        'categoria_sel' => json_encode([
            'id' => $this->categoria_id,
            'nombre' => Categoria::find($this->categoria_id)->nombre,
        ]),
        'producto_sel' => json_encode([
            'id' => $this->producto_id,
            'nombre' => Producto::find($this->producto_id)->nombre,
        ]),
        'caracteristicas_sel' => json_encode($this->caracteristicas_sel),
        'opciones_sel' => json_encode($this->opciones_sel),
        'flag_reconfigurar' => 0,
        'flag_solicitud_reconfigurar' => 0,
        'total_piezas_sel' => json_encode([
            'total' => $totalPiezasFinal,
            'detalle_tallas' => $this->mostrarFormularioTallas ? $this->tallasSeleccionadas : null,
        ]),
        'flag_requiere_proveedor' => (int) $this->flag_requiere_proveedor,
    ];

    // Si se eligió un nuevo usuario, actualizar también el proyecto
    if (!is_null($this->usuario_id_nuevo)) {
        $updateData['usuario_id'] = (int) $this->usuario_id_nuevo;
    }

    $preProyecto->update($updateData);

    // --- Si cambió el usuario, validar que sea CLIENTE (Spatie roles.tipo=1) y resolver/crear cliente_id ---
    // if (!is_null($this->usuario_id_nuevo)) {

    //     $cliente = Cliente::where('usuario_id', (int) $this->usuario_id_nuevo)->first();

    //     if (!$cliente) {
    //         $this->addError('usuario_id_nuevo', 'El usuario es cliente (rol), pero aún no tiene registro en la tabla clientes.');
    //         return;
    //     }

    //     Pedido::where('proyecto_id', $this->ProyectoId)
    //         ->whereIn('estatus', ['POR APROBAR', 'POR REPROGRAMAR'])
    //         ->update(['cliente_id' => $cliente->id]);
    // }

    // Pedidos a reconfigurar (usar estatus)
    $pedidos = Pedido::where('proyecto_id', $this->ProyectoId)
        ->whereIn('estatus', ['POR APROBAR', 'POR REPROGRAMAR'])
        ->get();

    foreach ($pedidos as $pedido) {

        $pedido->update([
            'producto_id' => $this->producto_id,
            'total' => $totalPiezasFinal,
            'estatus' => 'POR APROBAR',
        ]);

        // Rehacer pivotes
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

        // Tallas (si aplica)
        if ($this->mostrarFormularioTallas && is_array($this->tallasSeleccionadas)) {
            foreach ($this->tallasSeleccionadas as $grupoId => $tallas) {
                foreach ($tallas as $tallaId => $cantidad) {
                    PedidoTalla::create([
                        'pedido_id' => $pedido->id,
                        'talla_id' => $tallaId,
                        'grupo_talla_id' => $grupoId,
                        'cantidad' => 0, // se inicializa en cero por tu flujo
                    ]);
                }
            }
        }

        // Log estado
        PedidoEstado::create([
            'pedido_id' => $pedido->id,
            'proyecto_id' => $this->ProyectoId,
            'usuario_id' => auth()->id(),
            'estado' => 'Se reprograma el pedido',
            'fecha_inicio' => now(),
            'fecha_fin' => null,
        ]);
    }

    $this->registrarEventoEnChat('El Usuario generó la reconfiguración del proyecto.');

    session()->flash('message', 'Preproyecto actualizado exitosamente.');
    return redirect()->route('proyecto.show', $this->ProyectoId);
}


    protected function registrarEventoEnChat($mensaje)
    {
        $proyecto = Proyecto::find($this->ProyectoId); // <- P mayúscula
        if (!$proyecto) return;

        $chat = $proyecto->chat ?? $proyecto->chat()->create([
            'proyecto_id' => $proyecto->id,
            'fecha_creacion' => now(),
        ]);

        $chat->mensajes()->create([
            'usuario_id' => Auth::id(),
            'mensaje' => $mensaje,
            'tipo' => 2,
            'fecha_envio' => now(),
        ]);
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
        $this->productos = $this->categoria_id
            ? Producto::where('categoria_id', $this->categoria_id)->get()
            : collect();
        $this->flag_requiere_proveedor = 0;
        $this->despligaformopciones();
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

                if ($producto) {
                    $this->flag_requiere_proveedor = (int) ($producto->flag_requiere_proveedor ?? 0);
                } else {
                    $this->flag_requiere_proveedor = 0;
                }

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

public function updatedUsuarioQuery($value): void
{
    $term = trim($value);

    if ($term === '') {
        $this->usuariosSugeridos = [];
        return;
    }

    $this->usuariosSugeridos = User::query()
        ->select('users.id', 'users.name', 'users.email')
        ->whereHas('roles', function ($q) {
            $q->where('roles.tipo', 1); // 1 = CLIENTE
        })
        ->where(function ($q) use ($term) {
            $q->where('users.name', 'like', "%{$term}%")
              ->orWhere('users.email', 'like', "%{$term}%");
        })
        ->orderBy('users.name')
        ->limit(15)
        ->get()
        ->toArray();
}

    public function selectUsuario(int $id): void
    {
        $this->usuario_id_nuevo = $id;
    }

    

    public function setReadOnlyMode()
    {
        $this->dispatch('setReadOnlyMode');
    }
}



//return view('livewire.preproyectos.edit-pre-project');

//  EditProject     return view('livewire.reprogramacion.edit-project');