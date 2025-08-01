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

        $this->nombre = $preProyecto->nombre;
        $this->descripcion = $preProyecto->descripcion;

       
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
        $this->cargarTiposEnvio();

        $this->id_tipo_envio = $preProyecto->id_tipo_envio;

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





        if ($this->seleccion_armado === null || $this->seleccion_armado === '') {
            $this->seleccion_armado = 1;
        }

      //  $totalPiezasFinal = $this->mostrarFormularioTallas ? array_sum($this->tallasSeleccionadas) : $this->total_piezas;
        $totalPiezasFinal = $this->mostrarFormularioTallas ? array_sum((array) $this->tallasSeleccionadas) : $this->total_piezas;
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
                'detalle_tallas' => $this->mostrarFormularioTallas ? $this->tallasSeleccionadas : null
            ]),
            'flag_armado'=> $this->seleccion_armado,
            'direccion_fiscal_id' => $this->direccion_fiscal_id,
            'direccion_entrega_id' => $this->direccion_entrega_id,
            'id_tipo_envio' => $this->id_tipo_envio,
        ]);

        // Guardar nuevos archivos
        foreach ($this->files as $index => $file) {
            $path = $file->store('archivos_proyectos', 'public');
            ArchivoProyecto::create([
                'pre_proyecto_id' => $this->preProyectoId,
                'usuario_id' => Auth::id(),
                'nombre_archivo' => $file->getClientOriginalName(),
                'ruta_archivo' => $path,
                'tipo_archivo' => $file->getClientMimeType(),
                'descripcion' => $this->fileDescriptions[$index] ?? '',
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

                           // Ajustar las fechas para que no caigan en sábado o domingo
                $fecha_embarque = Carbon::parse($this->ajustarFechaSinFinesDeSemana($fecha_embarque));
                $fecha_produccion = Carbon::parse($this->ajustarFechaSinFinesDeSemana($fecha_produccion));
            
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

    public function validarFechaEntrega()
    {
        if ($this->fecha_entrega) {
            $fecha = Carbon::parse($this->fecha_entrega);
            $diaSemana = $fecha->dayOfWeek; // 0 = Domingo, 6 = Sábado

            if ($diaSemana === 6) {
                // Si es sábado, mover al lunes siguiente
                $fecha->addDays(2);
            } elseif ($diaSemana === 0) {
                // Si es domingo, mover al lunes siguiente
                $fecha->addDay();
            }

            // Asignar la nueva fecha corregida
            $this->fecha_entrega = $fecha->format('Y-m-d');

            $this->on_Calcula_Fechas_Entrega();
        }
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
        // 1) Recupera y marca
        $archivo = ArchivoProyecto::where('id', $fileId)
            ->where('pre_proyecto_id', $this->preProyectoId)
            ->firstOrFail();

        $archivo->update(attributes: ['flag_descarga' => 1]);

        // Refresca la lista para actualizar el badge “(Descargado)”
        $this->existingFiles = ArchivoProyecto::where('pre_proyecto_id', $this->preProyectoId)->get();

        // 2) Delegar en el modelo la descarga real
        return $archivo->descargar();
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
}



//return view('livewire.preproyectos.edit-pre-project');