<?php

namespace App\Livewire\Preproyectos;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\user;
use App\Models\PreProyecto;
use App\Models\DireccionEntrega;
use App\Models\DireccionFiscal;
use App\Models\Ciudad;
use App\Models\Pais;
use App\Models\Estado;
use App\Models\TipoEnvio;
use App\Models\Cliente;

use App\Models\ArchivoProyecto;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Caracteristica;
use App\Models\Talla;
use App\Models\Opcion;
use App\Models\Chat;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use Livewire\Attributes\On;

use Illuminate\Support\Facades\Log;

class CreatePreProject extends Component
{
    use WithFileUploads;

    public $UsuarioSeleccionado;

    public $nombre;
    public $descripcion;
    public $fecha_produccion;
    public $fecha_embarque;
    public $fecha_entrega;

    // Variables para archivos
    public $files = [];
    public $fileDescriptions = [];
    public $uploadedFiles = [];

    public $usuariosSeleccionados= [];

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

    public $clientes = [];
    public $cliente_id; // Cliente seleccionado
    public $mostrarModalCliente = false;


    public $seleccion_armado = null;
    public $mostrar_selector_armado = false;


    public $direccionesFiscales = [];
    public $direccionesEntrega = [];

    // Propiedades para crear un nuevo cliente
    public $nuevoCliente = [
        'nombre_empresa' => '',
        'contacto_principal' => '',
        'telefono' => '',
        'email' => '',
    ];

    public $isUploading = false;

    protected $listeners = [
        'livewire-upload-start' => 'uploadStarted',
        'livewire-upload-finish' => 'uploadFinished',
        'livewire-upload-error' => 'uploadFinished',
    ];




    public function create()
    {

        Log::debug('Pre Pruebas');


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

            'total_piezas' => $this->mostrarFormularioTallas ? 'required|integer|min:1' : 'required|integer|min:1',
            'tallasSeleccionadas' => $this->mostrarFormularioTallas ? 'required|array|min:1' : 'nullable',
            'files.*' => 'nullable|file|max:10240',
        ]);

        
        if (count($this->files) > 4) {
            $this->addError('files', 'Solo puedes subir hasta 4 archivos.');
            return;
        }


        if ($this->mostrar_selector_armado) {
            $rules['seleccion_armado'] = 'required|in:0,1';
        }
    
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


        // $cliente_id = $this->cliente_id ?? null;

        // if (!$cliente_id) {
        //     session()->flash('error', 'El usuario autenticado no tiene un cliente asociado.');
        //     return;
        // }


        Log::debug('No error caracteristica');

        $totalPiezasFinal = $this->mostrarFormularioTallas ? array_sum($this->tallasSeleccionadas) : $this->total_piezas;

        //antes de crear asignamos fechas
        $this->on_Calcula_Fechas_Entrega();

        // Asignamos copnjunto direccion entrega


        // Obtener los nombres de país, estado y ciudad para la dirección de entrega
        $direccionEntrega = DireccionEntrega::find($this->direccion_entrega_id);
        $pais_name = $direccionEntrega->ciudad->estado->pais->nombre ?? '';
        $estado_name = $direccionEntrega->ciudad->estado->nombre ?? '';
        $ciudades_name = $direccionEntrega->ciudad->nombre ?? '';

        // Obtener los nombres de país, estado y ciudad para la dirección fiscal
        $direccionFiscal = DireccionFiscal::find($this->direccion_fiscal_id);
        $fiscal_pais_name = $direccionFiscal->ciudad->estado->pais->nombre ?? '';
        $fiscal_estado_name = $direccionFiscal->ciudad->estado->nombre ?? '';
        $fiscal_ciudades_name = $direccionFiscal->ciudad->nombre ?? '';

        // Construcción de dirección como texto
        $Auxiliar_direccion_entrega = trim("$ciudades_name, $estado_name, $pais_name");
        $Auxiliar_direccion_fiscal = trim("$fiscal_ciudades_name, $fiscal_estado_name, $fiscal_pais_name");


        // Filtrar solo grupos que tienen tallas con cantidad > 0
        $tallasEstructuradas = collect($this->tallasSeleccionadas)
            ->map(function ($tallas) {
                return collect($tallas)->map(fn($cantidad) => intval($cantidad))->toArray();
            })
            ->toArray();


            if ($this->seleccion_armado === null || $this->seleccion_armado === '') {
                $this->seleccion_armado = 1;
            }

        $preProyecto = PreProyecto::create([
            'usuario_id' => $this->UsuarioSeleccionado,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'tipo' => 'PROYECTO',
            'numero_muestras' => 0,
            'estado' => 'PENDIENTE',
            'fecha_produccion' => $this->fecha_produccion,
            'fecha_embarque' => $this->fecha_embarque,
            'fecha_entrega' => $this->fecha_entrega,
            'flag_armado'=> $this->seleccion_armado,
            'categoria_sel' => json_encode(['id' => $this->categoria_id, 'nombre' => Categoria::find($this->categoria_id)->nombre]),
            'producto_sel' => json_encode(['id' => $this->producto_id, 'nombre' => Producto::find($this->producto_id)->nombre]),
            'caracteristicas_sel' => json_encode($this->caracteristicas_sel),
            'opciones_sel' => json_encode($this->opciones_sel),
            'direccion_entrega_id'=>$this->direccion_entrega_id,
            'direccion_entrega'=> $Auxiliar_direccion_entrega,
            'direccion_fiscal_id'=>$this->direccion_fiscal_id,
            'direccion_fiscal'=> $Auxiliar_direccion_fiscal,
            'id_tipo_envio' => $this->id_tipo_envio,
            'total_piezas_sel' => json_encode([
                'total' => intval($this->total_piezas), 
                'detalle_tallas' => $this->mostrarFormularioTallas ?  $tallasEstructuradas : null
            ]),
            'cliente_id'=> null

        ]);




        // Guardar archivos
        foreach ($this->files as $index => $file) {
            $path = $file->store('archivos_proyectos', 'public');

            ArchivoProyecto::create([
                'pre_proyecto_id' => $preProyecto->id,
                'usuario_id' => Auth::id(),
                'nombre_archivo' => $file->getClientOriginalName(),
                'ruta_archivo' => $path,
                'tipo_archivo' => $file->getClientMimeType(),
                'descripcion' => $this->fileDescriptions[$index] ?? '',
                'tipo_carga' => 2
            ]);
        }

        session()->flash('message', 'Preproyecto creado exitosamente.');
        return redirect()->route('preproyectos.index');
    }

    public function mount()
    {

    $user = Auth::user();
    $config = $user->config ?? [];
    $puedeSeleccionar = $config['flag-can-user-sel-preproyectos'] ?? false;

    $this->UsuarioSeleccionado = $puedeSeleccionar ? null : $user->id;
    $this->direccionesFiscales = collect();
    $this->direccionesEntrega = collect();


        $this->tallasSeleccionadas = [];

        $this->cargarClientes();
        $this->cargarDirecciones();
    
        // Verificar si la relación existe antes de acceder a ella
        foreach (Talla::with('gruposTallas')->get() as $talla) {
            if ($talla->gruposTallas->isNotEmpty()) {
                foreach ($talla->gruposTallas as $grupo) {
                    $this->tallasSeleccionadas[$grupo->id][$talla->id] = 0;
                }
            }
        }
    
        Log::debug('Estructura de tallasSeleccionadas después de mount()', ['data' => $this->tallasSeleccionadas]);
    }

        public function render()
        {
            return view('livewire.preproyectos.create-pre-project', [
                'categorias' => Categoria::where('ind_activo', 1)->get(),
                'productos' => $this->productos,
                'tiposEnvio' => $this->tipos_envio,
                'mostrarFormularioTallas'=> $this->mostrarFormularioTallas,
                'direccionesFiscales' => $this->direccionesFiscales,
                'direccionesEntrega' => $this->direccionesEntrega,
                'todosLosUsuarios' => User::query()
                    ->whereIn('id', auth()->user()->user_can_sel_preproyectos ?? [])
                    ->whereJsonContains('config->flag-user-sel-preproyectos', true)
                    ->get(),
            ]);
        }




    public function onCategoriaChange()
    {

        $this->producto_id = null;
        $this->productos = Producto::where('categoria_id', $this->categoria_id)->where('ind_activo', 1)->get();

            //Limpiasmos las ociones y caracteristicas 
            $this->tallas = collect(); // Vaciar antes de asignar nuevas tallas
            $this->tallasSeleccionadas = [];


            $this->mostrarFormularioTallas = 0;
            $this->caracteristicas_sel = [];
            $this->opciones_sel = [];
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

            // Calcular fechas de entrega
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
            
               $fecha_embarque = $this->restarDiasHabiles($fecha_entrega, $dias_envio);
               $fecha_produccion = $this->restarDiasHabiles($fecha_embarque, $dias_produccion_producto);

                           // Ajustar las fechas para que no caigan en sábado o domingo
                // $fecha_embarque = Carbon::parse($this->ajustarFechaSinFinesDeSemana($fecha_embarque));
                // $fecha_produccion = Carbon::parse($this->ajustarFechaSinFinesDeSemana($fecha_produccion));
            
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

    public function restarDiasHabiles($fecha, $dias)
    {
        $fecha = Carbon::parse($fecha);
        $contador = 0;
    
        while ($contador < $dias) {
            $fecha->subDay();
            // Si el día es lunes a viernes
            if ($fecha->isWeekday()) {
                $contador++;
            }
        }
    
        return $fecha;
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
    
    // Función para cargar clientes del usuario autenticado
    public function cargarClientes()
    {
    Log::debug('Carga Clientes');

        $this->clientes = Cliente::where('usuario_id', $this->UsuarioSeleccionado)->get();
    }

        // Función para guardar un nuevo cliente
    public function guardarCliente()
    {
        $this->validate([
            'nuevoCliente.nombre_empresa' => 'required|string|max:255',
            'nuevoCliente.contacto_principal' => 'nullable|string|max:255',
            'nuevoCliente.telefono' => 'nullable|string|max:20',
            'nuevoCliente.email' => 'nullable|email|max:255',
        ]);

        // Crear el cliente y asociarlo al usuario autenticado
        $cliente = Cliente::create([
            'usuario_id' => $this->UsuarioSeleccionado,
            'nombre_empresa' => $this->nuevoCliente['nombre_empresa'],
            'contacto_principal' => $this->nuevoCliente['contacto_principal'],
            'telefono' => $this->nuevoCliente['telefono'],
            'email' => $this->nuevoCliente['email'],
        ]);

        // Cargar clientes actualizados
        $this->cargarClientes();

        // Seleccionar el nuevo cliente
        $this->cliente_id = $cliente->id;

        // Cerrar el modal
        $this->mostrarModalCliente = false;

        // Reiniciar datos del formulario de cliente
        $this->reset('nuevoCliente');

        session()->flash('message', 'Cliente agregado correctamente.');
    }


public function usuarioSeleccionadoCambio($usuarioId)
{
    $this->UsuarioSeleccionado = $usuarioId;
    $this->cargarClientes();
    $this->cargarDirecciones();
}

public function cargarDirecciones()
{
    Log::debug('Carga Direcciones');

    if ($this->UsuarioSeleccionado) {
        $this->direccionesFiscales = DireccionFiscal::where('usuario_id', $this->UsuarioSeleccionado)->get();
        $this->direccionesEntrega = DireccionEntrega::where('usuario_id', $this->UsuarioSeleccionado)->get();

        // Opcional: asignar automáticamente la primera dirección si no hay una seleccionada
        // if (!$this->direccion_fiscal_id && $this->direccionesFiscales->isNotEmpty()) {
        //     $this->direccion_fiscal_id = $this->direccionesFiscales->first()->id;
        // }

        // if (!$this->direccion_entrega_id && $this->direccionesEntrega->isNotEmpty()) {
        //     $this->direccion_entrega_id = $this->direccionesEntrega->first()->id;
        // }

    } else {
        $this->direccionesFiscales = collect();
        $this->direccionesEntrega = collect();
    }
}

public function updatedDireccionFiscalId($value)
{
    $this->direccion_fiscal_id = (int) $value;
}

public function updatedDireccionEntregaId($value)
{
    $this->direccion_entrega_id = (int) $value;
    $this->cargarTiposEnvio();
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
