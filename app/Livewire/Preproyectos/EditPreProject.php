<?php

namespace App\Livewire\Preproyectos;


use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\PreProyecto;
use App\Models\DireccionEntrega;
use App\Models\DireccionFiscal;
use App\Models\Ciudad;
use App\Models\Pais;
use App\Models\Estado;
use App\Models\TipoEnvio;
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
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Builder;

class EditPreProject extends Component
{
    use WithFileUploads;

    public $preProyectoId;
    // public $nombre;
    // public $descripcion;
    // public $fecha_produccion;
    // public $fecha_embarque;
    // public $fecha_entrega;

    // public $categoria_id;
    // public $producto_id;
    // public $productos = [];
    // public $caracteristicas_sel = [];
    // public $opciones_sel = [];

    // public $total_piezas;
    // public $tallas = [];
    // public $tallasSeleccionadas = [];
    // public $mostrarFormularioTallas = false;

    public $direccion_fiscal;
    public $direccion_entrega;
    // public $id_tipo_envio;
    // public $tipos_envio = [];

    // public $mensaje_produccion;

    // // Archivos
    // public $files = [];
    // public $fileDescriptions = [];
    // public $uploadedFiles = [];
    public $existingFiles = [];

    // Selector de usuario (solo clientes)
    public ?int $UsuarioSeleccionado = null;

    public string $usuarioQuery = '';
    public ?int $usuario_id_nuevo = null;
    public array $usuariosSugeridos = [];
    public bool $puedeBuscarUsuarios = false;

    // Direcciones dependientes del usuario seleccionado
    public $direccionesFiscales = [];
    public $direccionesEntrega = [];

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
    public $producto_flag_armado;
    public $productos = [];
    public $caracteristicas_sel = [];
    public $opciones_sel = [];

    public $flag_requiere_proveedor = 0;

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

    public $modoLectura = false;
    

    public $isUploading = false;

    protected $listeners = [
        'livewire-upload-start' => 'uploadStarted',
        'livewire-upload-finish' => 'uploadFinished',
        'livewire-upload-error' => 'uploadFinished',
    ];



    public function mount($preProyectoId)
    {
        $this->preProyectoId = $preProyectoId;


        

        $preProyecto = PreProyecto::findOrFail($preProyectoId);


                // Usuario dueño del preproyecto
        $this->UsuarioSeleccionado = (int) $preProyecto->usuario_id;
        $this->usuario_id_nuevo = $this->UsuarioSeleccionado;

        // Inicializa selector (permisos/subordinados) + sugeridos
        $this->setupUsuarioSelector();
        $this->refreshUsuariosSugeridos(true);

        // Cargar direcciones del usuario seleccionado
        $this->cargarDirecciones();



        $this->nombre = $preProyecto->nombre;
        $this->descripcion = $preProyecto->descripcion;

        $this->flag_requiere_proveedor = (int) ($preProyecto->flag_requiere_proveedor ?? 0);

       
        $this->fecha_produccion = Carbon::parse($preProyecto->fecha_produccion)->format('Y-m-d');

        $this->fecha_embarque = Carbon::parse($preProyecto->fecha_embarque)->format('Y-m-d');


        $this->fecha_entrega = Carbon::parse($preProyecto->fecha_entrega)->format('Y-m-d');

        $this->categoria_id = json_decode($preProyecto->categoria_sel)->id;
    
        $this->producto_id = json_decode($preProyecto->producto_sel)->id;

        $this->total_piezas = json_decode($preProyecto->total_piezas_sel)->total ?? 0;
        
        $this->direccion_fiscal = $preProyecto->direccion_fiscal;
        $this->direccion_fiscal_id = $preProyecto->direccion_fiscal_id;
        $this->direccion_entrega = $preProyecto->direccion_entrega;
        $this->direccion_entrega_id = $preProyecto->direccion_entrega_id;

        // cargamos los tipos de envio
        $this->id_tipo_envio = $preProyecto->id_tipo_envio; // primero
        $this->cargarTiposEnvio(); // después (ya trae tipos por estado)
        // y fuerza cálculo final (por si cargarTiposEnvio autoselecciona o resetea)
        $this->on_Calcula_Fechas_Entrega();

        // Cargar productos de la categoría seleccionada
        $this->productos = Producto::where('categoria_id', $this->categoria_id)->get();
        $this->producto_flag_armado = Producto::where('id', $this->producto_id)->value('flag_armado');
        Log::debug('producto_flag_armado', ['data' => $this->producto_flag_armado]);

        // Evaluamos si el producto seleccionado puede mostrar la pregunta de armado 
        
        // si si asignamos el valor del imput
        if ( $this->producto_flag_armado == 1) {
            $this->mostrar_selector_armado = true;
            // asignamos el valor del armado del select 
            $this->seleccion_armado =  $preProyecto->flag_armado;
        } else {
            $this->mostrar_selector_armado = false;
        }

        Log::debug('In mount producto_id:', ['data' => $this->producto_id]);
        // Cargar tallas si es "Playeras"
        $categoria = Categoria::find($this->categoria_id);

        // Carga lascategorias
        Log::debug('Mount despligaformopciones');
        $this -> despligaformopciones();


        // Selecciona las opciones seleccionadas
        $this->enviarOpcionesSeleccionadas();



       //$this->mostrarFormularioTallas = $categoria && strtolower($categoria->nombre) === 'playeras';
        $this->tallas = Talla::all();
        //$this->tallasSeleccionadas = json_decode($preProyecto->total_piezas_sel)->detalle_tallas ?? [];
        $this->tallasSeleccionadas = json_decode($preProyecto->total_piezas_sel, true)['detalle_tallas'] ?? [];


        // Cargar archivos existentes
        $this->existingFiles = ArchivoProyecto::where('pre_proyecto_id', $this->preProyectoId)->get();


            // Bloquear modo edición si el usuario tiene rol "estaf"
            if (Auth::user()->hasRole('estaf')) {
                $this->modoLectura = true;
            }
    }

    public function update( $from )
    {

        // 1 preguardado
        // 2 preAprobarProyecto

        $this->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_entrega' => 'nullable|date',
            'categoria_id' => 'required|exists:categorias,id',
            'producto_id' => 'required|exists:productos,id',
            'direccion_fiscal_id' => 'required',
            'direccion_entrega_id' => 'required',
            'id_tipo_envio' => 'required',
            // 'total_piezas' => $this->mostrarFormularioTallas ? 'nullable' : 'required|integer|min:1',
            // 'tallasSeleccionadas' => $this->mostrarFormularioTallas ? 'required|array|min:1' : 'nullable',
            
        ]);



        
        Log::debug('PRE error mostrarFormularioTallas');
        // **Si hay tallas, la suma de tallas debe ser igual a total_piezas**
        if ($this->mostrarFormularioTallas) {
            // Filtrar solo los grupos que realmente contienen tallas
            $gruposValidos = array_filter($this->tallasSeleccionadas, 'is_array');
        
            // Sumar todas las cantidades de tallas correctamente
            $sumaTallas = collect($gruposValidos)->flatMap(function ($grupo) {
                return array_values($grupo);
            })->sum();
        
            Log::debug('Suma calculada de tallas', ['data' => $sumaTallas]);
            Log::debug('Total de piezas ingresado', ['data' => $this->total_piezas]);
        
            if ($sumaTallas != $this->total_piezas) {
                $this->addError('total_piezas', 'La suma de las cantidades de tallas debe ser igual al total de piezas.');
                return;
            }
        }



        Log::debug('No error mostrarFormularioTallas');

        // 🚨 Validación: Cada característica debe tener al menos una opción en `opciones`
        foreach ($this->caracteristicas_sel as $caracteristica) {
            if (empty($caracteristica['opciones']) || count($caracteristica['opciones']) == 0) {
                $this->addError('caracteristicas_sel', "Debe seleccionar al menos una opción para '{$caracteristica['nombre']}'.");
                return;
            }
        }


        Log::debug('validacion',);
        $archivosPendientes = ArchivoProyecto::where('pre_proyecto_id', $this->preProyectoId)
                ->where('flag_descarga', 0)
                ->count();

        Log::debug('Rchivos pendientes ', ['data' => $archivosPendientes]);

        if( $from == 2){
            if ($archivosPendientes > 0  ) {
                // añade un error bajo la “clave” archivosPendientes
                    Log::debug('Despliega el error ');
                $this->addError('archivosPendientes', 'Debes descargar todos los archivos antes de Aprobar.');
                return;
            }
        }



        $direccion = DireccionEntrega::find($this->direccion_entrega_id);
        if ($direccion && $direccion->estado_id) {
            $permitidos = Estado::find($direccion->estado_id)
                ?->tipoEnvios()
                ->pluck('tipo_envio.id')
                ->toArray() ?? [];

            if (!in_array((int)$this->id_tipo_envio, array_map('intval', $permitidos), true)) {
                $this->addError('id_tipo_envio', 'El tipo de envío no pertenece al estado de la dirección seleccionada.');
                return;
            }
        }



        if ($this->seleccion_armado === null || $this->seleccion_armado === '') {
            $this->seleccion_armado = 1;
        }

      //  $totalPiezasFinal = $this->mostrarFormularioTallas ? array_sum($this->tallasSeleccionadas) : $this->total_piezas;
        $totalPiezasFinal = (int) $this->total_piezas;

        if ($this->mostrarFormularioTallas) {
            $gruposValidos = array_filter($this->tallasSeleccionadas, 'is_array');

            $totalPiezasFinal = (int) collect($gruposValidos)
                ->flatMap(fn($grupo) => array_values($grupo))
                ->sum();
        }

        // Actualizar el preproyecto
        $preProyecto = PreProyecto::findOrFail($this->preProyectoId);
        $preProyecto->update([
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'fecha_entrega' => $this->fecha_entrega,
            'categoria_sel' => json_encode(['id' => $this->categoria_id, 'nombre' => Categoria::find($this->categoria_id)->nombre]),
            'producto_sel' => json_encode(['id' => $this->producto_id, 'nombre' => Producto::find($this->producto_id)->nombre]),
            'caracteristicas_sel' => json_encode($this->caracteristicas_sel),
            'total_piezas_sel' => json_encode([
                'total' => $totalPiezasFinal,
                'flag_tallas' => (int) $this->mostrarFormularioTallas,
                'detalle_tallas' => $this->mostrarFormularioTallas ? $this->tallasSeleccionadas : null
            ]),
            'flag_armado'=> $this->seleccion_armado,
            'direccion_fiscal_id' => $this->direccion_fiscal_id,
            'direccion_entrega_id' => $this->direccion_entrega_id,
            'id_tipo_envio' => $this->id_tipo_envio,
            'flag_requiere_proveedor' => $this->flag_requiere_proveedor,
        ]);

        // Guardar nuevos archivos
        foreach ($this->files as $index => $file) {
            $path = $file->store('archivos_proyectos', 'public');

            $archivo = ArchivoProyecto::create([
                'pre_proyecto_id' => $this->preProyectoId,
                'usuario_id'      => Auth::id(),
                'nombre_archivo'  => $file->getClientOriginalName(),
                'ruta_archivo'    => $path,
                'tipo_archivo'    => $file->getClientMimeType(),
                'descripcion'     => $this->fileDescriptions[$index] ?? '',
                'log'             => [], // inicializamos
            ]);

            $this->appendArchivoLog($archivo, 'subido', [
                'tipo_carga'     => (int)($archivo->tipo_carga ?? 1),
                'mime'           => $archivo->tipo_archivo,
                'ruta'           => $archivo->ruta_archivo,
                'nombre'         => $archivo->nombre_archivo,
                'descripcion'    => $archivo->descripcion,
                'pre_proyecto_id'=> $archivo->pre_proyecto_id,
                'proyecto_id'    => $archivo->proyecto_id,
            ]);
        }

        if($from == 2 ){

            // logica de la aprbacion se coloco de este lado para hacer validas las validaciones implementadas 
            $preProyecto = PreProyecto::findOrFail($this->preProyectoId);

                Log::debug('preProyecto', ['data' => $preProyecto]);
            
                // Transferir el preproyecto a proyecto
                $proyecto = $preProyecto->transferirAProyecto();

                // Mensaje de éxito y redirección
                session()->flash('message', 'El proyecto ha sido aprobado y transferido correctamente.');

              return redirect()->route('preproyectos.index');
        }else{
             session()->flash('message', 'Preproyecto actualizado exitosamente.');

             return redirect()->route('preproyectos.index');
        }


    }

    public function deleteFile($fileId)
    {
        $file = ArchivoProyecto::findOrFail($fileId);

        // Log antes de borrar
        $this->appendArchivoLog($file, 'eliminado', [
            'ruta'   => $file->ruta_archivo,
            'nombre' => $file->nombre_archivo,
            'motivo' => 'eliminado_manual', // puedes cambiarlo
        ]);

        Storage::disk('public')->delete($file->ruta_archivo);
        $file->delete();

        $this->existingFiles = ArchivoProyecto::where('pre_proyecto_id', $this->preProyectoId)->get();
    }

    public function preguardado(){

        $this ->  update( 1);
        
       
        // return redirect()->route('preproyectos.index');
    }

    public function preAprobarProyecto()
    {

        $this->update(2);


  
    }


   // Funciones

    public function render()
    {

        // 'categorias' => Categoria::all(),
        // 'productos' => $this->productos,
        // 'tiposEnvio' => TipoEnvio::all(),


        return view('livewire.preproyectos.edit-pre-project', [

            'categorias' => Categoria::all(),
            'productos' => $this->productos,
            'tiposEnvio' => $this->tipos_envio,
            'mostrarFormularioTallas'=> $this->mostrarFormularioTallas,
            'direccionesFiscales' => $this->direccionesFiscales,
            'direccionesEntrega'  => $this->direccionesEntrega,
        ]);
    }


    

    public function onCategoriaChange()
    {

        $this->producto_id = null;
        $this->productos = Producto::where('categoria_id', $this->categoria_id)->get();
         $this->flag_requiere_proveedor = 0;

    }

    public function despliega_form_tallas()
    {
        // Verifica si la categoría seleccionada requiere tallas
        $categoria = Categoria::find($this->categoria_id);
        $this->mostrarFormularioTallas = $categoria && $categoria->flag_tallas == 1;

        Log::debug('IN despliega_form_tallas mostrarFormularioTallas', ['data' =>  $this->mostrarFormularioTallas]);
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
        
        
            Log::debug('Inicio despligaformopciones');

            $this->despliega_form_tallas(); // Obtiene las tallas según el nuevo producto

            // Asegurar que el producto ha sido seleccionado
            Log::debug('producto_id:', ['data' => $this->producto_id]);
            if (!$this->producto_id) {
                Log::debug('Producto seleccionado');
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

            // Calcular fechas de entrega
            $this->on_Calcula_Fechas_Entrega();
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


    public function updatedDireccionEntregaId($value = null): void
    {
        $this->direccion_entrega_id = $value ? (int)$value : null;

        // limpia selección previa del tipo de envío
        $this->id_tipo_envio = null;
        $this->tipos_envio = [];

        $this->cargarTiposEnvio();
    }

    public function cargarTiposEnvio(): void
    {
        $this->tipos_envio = [];
        //$this->id_tipo_envio = null;
        // Solo limpiar si el tipo actual no pertenece a la nueva lista
        // (lo validamos al final)
        if (!$this->direccion_entrega_id) {
            return;
        }

        $direccion = DireccionEntrega::find($this->direccion_entrega_id);

        // ✅ Debe existir y tener estado_id (directo en la dirección)
        if (!$direccion || empty($direccion->estado_id)) {
            return;
        }

        $estado = Estado::find($direccion->estado_id);

        $this->tipos_envio = $estado
            ? $estado->tipoEnvios()->orderBy('nombre')->get()
            : [];

        // Opcional: autoselecciona si solo hay 1
        if (count($this->tipos_envio) === 1) {
            $this->id_tipo_envio = $this->tipos_envio[0]->id;
        }

        if (!empty($this->id_tipo_envio)) {
            $existe = collect($this->tipos_envio)->pluck('id')->contains((int)$this->id_tipo_envio);
            if (!$existe) {
                $this->id_tipo_envio = null;
            }
        }

        // recalcula si ya hay fecha de entrega
        $this->on_Calcula_Fechas_Entrega();
    }


    public function updatedFiles()
    {
        $this->uploadedFiles = [];

        foreach ($this->files as $file) {
            $mimeType = $file->getMimeType();

            $canPreview = str_starts_with($mimeType, 'image/');

            $this->uploadedFiles[] = [
                'name' => $file->getClientOriginalName(),
                'preview' => $canPreview ? $file->temporaryUrl() : null,
            ];
        }
    }

    public function on_Calcula_Fechas_Entrega(): void
    {
        if (!$this->fecha_entrega) {
            return;
        }

        // ✅ Si aún no hay tipo envío seleccionado, no calcules (evitas saltos raros)
        if (empty($this->id_tipo_envio)) {
            $this->fecha_embarque = null;
            $this->fecha_produccion = null;
            $this->mensaje_produccion = null;
            return;
        }

        Log::debug('Fecha de entrega ingresada', ['fecha_entrega' => $this->fecha_entrega]);

        $fecha_entrega = Carbon::parse($this->fecha_entrega);
        $fecha_entrega = $this->normalizaFechaLaboral($fecha_entrega);

        $ahora = Carbon::now();

        // defaults
        $dias_produccion_producto = 6;
        $dias_envio = 2;

        // ✅ Tipo de envío (ya viene filtrado por estado)
        $tipoEnvio = TipoEnvio::find($this->id_tipo_envio);
        if ($tipoEnvio) {
            $dias_envio = (int) $tipoEnvio->dias_envio;
            Log::debug('Días de envío (tipo)', ['dias_envio' => $dias_envio]);
        }

        // ✅ Producto: dias_produccion
        if (!empty($this->producto_id)) {
            $prod = Producto::find($this->producto_id);
            if ($prod) {
                $dias_produccion_producto = (int) ($prod->dias_produccion ?? $dias_produccion_producto);
                Log::debug('Días de producción (producto)', ['dias_produccion' => $dias_produccion_producto]);
            }
        }

        // ✅ Calcular restando DÍAS HÁBILES
        $fecha_embarque   = $this->restarDiasHabiles($fecha_entrega, $dias_envio);
        $fecha_produccion = $this->restarDiasHabiles($fecha_embarque, $dias_produccion_producto);

        $this->fecha_embarque   = $fecha_embarque->format('Y-m-d');
        $this->fecha_produccion = $fecha_produccion->format('Y-m-d');

        // Mensaje de producción vencida
        if ($fecha_produccion->lt($ahora)) {
            $this->mensaje_produccion = "⚠️ La fecha de producción calculada ({$this->fecha_produccion}) ya ha pasado. Se requiere una autorización adicional para continuar.";
            Log::warning('Este proyecto requiere autorización adicional para producción.');
        } else {
            $this->mensaje_produccion = null;
        }

        Log::debug('Fechas calculadas', [
            'fecha_entrega'    => $fecha_entrega->format('Y-m-d'),
            'fecha_embarque'   => $this->fecha_embarque,
            'fecha_produccion' => $this->fecha_produccion,
        ]);
    }

    public function validarFechaEntrega(): void
    {
        if (!$this->fecha_entrega) return;

        $fecha = Carbon::parse($this->fecha_entrega);
        $fecha = $this->normalizaFechaLaboral($fecha);

        $this->fecha_entrega = $fecha->format('Y-m-d');

        $this->on_Calcula_Fechas_Entrega();
    }

    public function ajustarFechaSinFinesDeSemana($fecha)
    {
        $fecha = Carbon::parse($fecha);
        $diaSemana = $fecha->dayOfWeek; // 0 = Domingo, 6 = Sábado

        if ($diaSemana === 6) {
            // Si es sábado, mover al lunes siguiente
            $fecha->addDays(2);
        } elseif ($diaSemana === 0) {
            // Si es domingo, mover al lunes siguiente
            $fecha->addDay();
        }

        return $fecha->format('Y-m-d');
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
        $preProyecto = PreProyecto::findOrFail($this->preProyectoId);
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
            PreProyecto::where('id', $this->preProyectoId)->update([
                'caracteristicas_sel' => json_encode($this->caracteristicas_sel)
            ]);



    }
    

    // public function descargarArchivo($fileId)
    // {
    //     $archivo = ArchivoProyecto::where('id', $fileId)
    //         ->where('pre_proyecto_id', $this->preProyectoId)
    //         ->firstOrFail();

    //     $archivo->update(['flag_descarga' => 1]);

    //     // Recargar archivos para reflejar el estado actualizado
    //     $this->existingFiles = ArchivoProyecto::where('pre_proyecto_id', $this->preProyectoId)->get();

    //     // Emitir evento JS para forzar la descarga
    //     $this->dispatch('archivoListoParaDescargar', url(Storage::url($archivo->ruta_archivo)));
    // }

    public function downloadFile(int $fileId)
    {
        $archivo = ArchivoProyecto::where('id', $fileId)
            ->where('pre_proyecto_id', $this->preProyectoId)
            ->firstOrFail();

        $antes = (int) ($archivo->flag_descarga ?? 0);

        // actualiza flag
        $archivo->update(['flag_descarga' => 1]);

        // agrega entrada "descargado" con antes/después correctos
        $this->appendArchivoLog($archivo, 'descargado', [
            'flag_descarga_antes'   => $antes,
            'flag_descarga_despues' => 1,
        ]);

        $this->existingFiles = ArchivoProyecto::where('pre_proyecto_id', $this->preProyectoId)->get();

        return $archivo->descargar();
    }


// --------- SOLO CLIENTES ----------
protected function baseClientesQuery(): Builder
{
    return \App\Models\User::query()
        ->whereHas('roles', fn($q) => $q->where('tipo', 1)); // 1=CLIENTE
}

protected function userEsCliente(int $userId): bool
{
    return \App\Models\User::whereKey($userId)
        ->whereHas('roles', fn($q) => $q->where('tipo', 1))
        ->exists();
}

// --------- PERMISOS / SUBORDINADOS ----------
protected function setupUsuarioSelector(): void
{
    $user = Auth::user();

    // Si no hay sesión, no se puede buscar
    if (!$user) {
        $this->puedeBuscarUsuarios = false;
        return;
    }

    // si estás en modo lectura, no permitas búsqueda
    if ($this->modoLectura) {
        $this->puedeBuscarUsuarios = false;
        return;
    }

    $puedeTodos = $user->can('preproyectos_seleccionar_todos_usuarios');
    $subIds     = $this->currentUserSubordinateIds();

    $this->puedeBuscarUsuarios = $puedeTodos || count($subIds) > 0;
}

protected function currentUserSubordinateIds(): array
{
    $user = Auth::user();
    $ids = [];

    if (method_exists($user, 'subordinates')) {
        $ids = $user->subordinates()->pluck('id')->all();
    } elseif (is_array(data_get($user, 'config.subordinates'))) {
        $ids = array_values(array_filter($user->config['subordinates']));
    } elseif (is_array(data_get($user, 'user_can_sel_preproyectos'))) {
        $ids = array_values(array_filter($user->user_can_sel_preproyectos));
    }

    return array_values(array_unique(array_map('intval', $ids)));
}

// --------- SUGERIDOS ----------
public function refreshUsuariosSugeridos(bool $bootstrap = false): void
{
    $user = Auth::user();
    $q = trim($this->usuarioQuery);

    $puedeTodos = $user->can('preproyectos_seleccionar_todos_usuarios');
    $subIds = $this->currentUserSubordinateIds();

    $builder = $this->baseClientesQuery();

    if (!$puedeTodos) {
        if (count($subIds) === 0) {
            $this->usuariosSugeridos = [];
            return;
        }
        $builder->whereIn('id', $subIds);
    }

    if ($q !== '') {
        $builder->where(function ($qq) use ($q) {
            $qq->where('name', 'like', "%{$q}%")
               ->orWhere('email', 'like', "%{$q}%");
        });
    }

    $limit = $puedeTodos ? 10 : 5;

    $this->usuariosSugeridos = $builder
        ->orderBy('name')
        ->limit($limit)
        ->get(['id', 'name', 'email'])
        ->toArray();

    // bootstrap: si no hay query, intenta al menos incluir al seleccionado actual si es cliente
    if ($bootstrap && $this->UsuarioSeleccionado && $this->userEsCliente((int)$this->UsuarioSeleccionado)) {
        $ya = collect($this->usuariosSugeridos)->pluck('id')->contains($this->UsuarioSeleccionado);
        if (!$ya) {
            $u = $this->baseClientesQuery()->whereKey($this->UsuarioSeleccionado)->first(['id','name','email']);
            if ($u) {
                array_unshift($this->usuariosSugeridos, $u->toArray());
            }
        }
    }
}

public function updatedUsuarioQuery(): void
{
    $this->refreshUsuariosSugeridos();
}

public function updatedUsuarioIdNuevo($value): void
{
    $id = (int) $value;

    if ($id && !$this->userEsCliente($id)) {
        $this->addError('UsuarioSeleccionado', 'Solo puedes seleccionar usuarios con rol tipo CLIENTE.');
        $this->usuario_id_nuevo = null;
        return;
    }

    $this->UsuarioSeleccionado = $id;
    $this->cargarDirecciones();

    // (Opcional) si necesitas notificar JS
    $this->dispatch('usuario-cambiado', id: $id);
}

// --------- DIRECCIONES POR USUARIO ----------
public function cargarDirecciones(): void
{
    if ($this->UsuarioSeleccionado) {
        $this->direccionesFiscales = DireccionFiscal::where('usuario_id', $this->UsuarioSeleccionado)->get();
        $this->direccionesEntrega  = DireccionEntrega::where('usuario_id', $this->UsuarioSeleccionado)->get();
    } else {
        $this->direccionesFiscales = collect();
        $this->direccionesEntrega  = collect();
    }
}


    public function setReadOnlyMode()
    {
        Log::debug('setReadOnlyMode');
        $this->dispatch('setReadOnlyMode');
    }

        
    public function uploadStarted()
    {
        $this->isUploading = true;
    }

    public function uploadFinished()
    {
        $this->isUploading = false;
    }


    protected function appendArchivoLog(ArchivoProyecto $archivo, string $accion, array $extra = []): void
    {
        // 1) Traer el valor actual "real" desde DB
        $archivo->refresh();

        // 2) Normalizar el log a array asociativo (puede venir string JSON)
        $current = $archivo->getAttribute('log');

        if (is_string($current)) {
            $current = json_decode($current, true);
        }
        if (!is_array($current)) {
            $current = [];
        }

        // Si viene como lista (0,1,2 numéricos), lo convertimos a objeto indexado "0","1","2"
        $isList = array_keys($current) === range(0, count($current) - 1);
        if ($isList) {
            $tmp = [];
            foreach ($current as $i => $row) {
                $tmp[(string)$i] = $row;
            }
            $current = $tmp;
        }

        // 3) Siguiente índice como string
        $idx = (string) count($current);

        // 4) Construir entrada (formato EXACTO como tu ejemplo)
        $current[$idx] = array_merge([
            'ip'                    => request()->ip(),
            'fecha'                 => now()->format('Y-m-d H:i:s'),
            'accion'                => $accion,          // "Cargado" / "descargado" / etc
            'usuario_id'            => Auth::id(),
            'flag_descarga_antes'   => (int) ($archivo->flag_descarga ?? 0),
            'flag_descarga_despues' => (int) ($archivo->flag_descarga ?? 0),
        ], $extra);

        // 5) Limitar tamaño (opcional)
        $max = 200;
        if (count($current) > $max) {
            // conservar los últimos $max manteniendo keys ordenadas
            $keys = array_keys($current);
            $sliceKeys = array_slice($keys, -$max);
            $current = array_intersect_key($current, array_flip($sliceKeys));
        }

        // 6) Guardar
        $archivo->forceFill(['log' => $current])->save();

        Log::debug('appendArchivoLog OK', [
            'archivo_id' => $archivo->id,
            'accion'     => $accion,
            'log_count'  => count($current),
        ]);
    }


        protected function restarDiasHabiles(Carbon $fecha, int $dias): Carbon
        {
            $f = $fecha->copy();
            $contador = 0;

            while ($contador < $dias) {
                $f->subDay();
                if ($f->isWeekday()) {
                    $contador++;
                }
            }

            return $f;
        }

        protected function normalizaFechaLaboral(Carbon $fecha): Carbon
        {
            // Si cae sábado o domingo, la movemos al lunes siguiente
            if ($fecha->isSaturday()) return $fecha->addDays(2);
            if ($fecha->isSunday())   return $fecha->addDay();
            return $fecha;
        }

}



//return view('livewire.preproyectos.edit-pre-project');