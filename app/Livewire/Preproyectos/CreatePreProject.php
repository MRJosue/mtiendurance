<?php

namespace App\Livewire\Preproyectos;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\TemporaryUploadedFile;
use App\Models\PreProyecto;
use App\Models\DireccionEntrega;
use App\Models\DireccionFiscal;
use App\Models\Pais;
use App\Models\Estado;
use App\Models\Cliente;
use App\Services\Preproyectos\DeliveryDatePlanner;
use App\Services\Preproyectos\PreProjectCreator;
use App\Services\Preproyectos\ProductConfigurationBuilder;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Talla;
use App\Models\Opcion;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;


use Illuminate\Database\Eloquent\Builder;



class CreatePreProject extends Component
{
    use WithFileUploads;

    public ?int $selectedUserId = null;

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
    public $shippingOptions = [];

    public $mensaje_produccion;

    public $clientes = [];
    public $cliente_id; // Cliente seleccionado
    public $mostrarModalCliente = false;


    public $seleccion_armado = null;
    public $mostrar_selector_armado = false;


    public $direccionesFiscales = [];
    public $direccionesEntrega = [];

    // Modal direcciones
    public bool $mostrarModalDireccion = false;
    public string $tipoDireccion = 'entrega';

    // en la cabecera de la clase
    public string $usuarioQuery = '';
    public ?int $selectedUserLookupId = null;
    public array $usuariosSugeridos = [];
    public array $caracteristicaOpcionesDisponibles = [];


    public bool $puedeBuscarUsuarios = false;


    public $flag_requiere_proveedor = 0;

    
    // Form genérico de dirección
    public array $formDireccion = [
        'rfc'            => '',
        'nombre_contacto'=> '',
        'nombre_empresa' => '',
        'calle'          => '',
        'pais_id'        => null,
        'estado_id'      => null,
        'ciudad'         => '',
        'codigo_postal'  => '',
        'telefono'       => '',
        'flag_default'   => false,
    ];


        
    // Catálogos para selects del modal
    public $paises = [];
    public $estados = [];
    public $ciudades = [];

    // Propiedades para crear un nuevo cliente
    public $nuevoCliente = [
        'nombre_empresa' => '',
        'contacto_principal' => '',
        'telefono' => '',
        'email' => '',
    ];

    public $isUploading = false;

    protected PreProjectCreator $preProjectCreator;
    protected DeliveryDatePlanner $deliveryDatePlanner;
    protected ProductConfigurationBuilder $productConfigurationBuilder;

    protected $listeners = [
        'livewire-upload-start' => 'uploadStarted',
        'livewire-upload-finish' => 'uploadFinished',
        'livewire-upload-error' => 'uploadFinished',
    ];

    public function boot(
        PreProjectCreator $preProjectCreator,
        DeliveryDatePlanner $deliveryDatePlanner,
        ProductConfigurationBuilder $productConfigurationBuilder
    ): void {
        $this->preProjectCreator = $preProjectCreator;
        $this->deliveryDatePlanner = $deliveryDatePlanner;
        $this->productConfigurationBuilder = $productConfigurationBuilder;
    }




    public function create()
    {
        $this->validate($this->creationRules());
        if (!$this->validateBusinessRules()) {
            return;
        }

        $this->ensureSelectedClientUser();
        if (!$this->selectedUserId || !$this->userEsCliente((int) $this->selectedUserId)) {
            $this->addError('selectedUserId', 'Debes seleccionar un usuario CLIENTE para crear el preproyecto.');
            return;
        }

        $this->on_Calcula_Fechas_Entrega();
        $preProyecto = $this->storePreProject();
        $this->storeUploadedFiles($preProyecto);

        session()->flash('message', 'Preproyecto creado exitosamente.');
        return redirect()->route('preproyectos.index');
    }

    public function mount()
    {

    $user = Auth::user();

    $this->setupUsuarioSelector();
    $this->refreshUsuariosSugeridos(true);
    $config = $user->config ?? [];
    $puedeSeleccionar = $config['flag-can-user-sel-preproyectos'] ?? false;

    // en mount(), al principio o después de $user = Auth::user();



    $this->selectedUserId = $puedeSeleccionar ? null : $user->id;
    $this->selectedUserLookupId = $this->selectedUserId;
    $this->direccionesFiscales = collect();
    $this->direccionesEntrega = collect();

  
    // Catálogos iniciales del modal
    $this->paises = Pais::orderBy('nombre')->get();
    $this->estados = [];
    $this->ciudades = [];


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
                'tiposEnvio' => $this->shippingOptions,
                'mostrarFormularioTallas'=> $this->mostrarFormularioTallas,
                'direccionesFiscales' => $this->direccionesFiscales,
                'direccionesEntrega' => $this->direccionesEntrega,

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
            $this->caracteristicaOpcionesDisponibles = [];

        $this->flag_requiere_proveedor = 0;
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

        $this->flag_requiere_proveedor = (int) ($producto?->flag_requiere_proveedor ?? 0);
        $this->mostrar_selector_armado = (bool) ($producto?->flag_armado ?? false);
        $this->seleccion_armado = null;

        if (!$this->mostrar_selector_armado) {
            $this->despligaformopciones();
        }
    }

    public function despligaformopciones()
    {
        $this->despliega_form_tallas();

        if (!$this->producto_id) {
            $this->resetProductConfigurationState();
            return;
        }

        $this->hydrateTallasForSelectedProduct();
        $this->buildCaracteristicasConfiguration();
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


  

    public function cargarTiposEnvio(): void
    {
        $this->shippingOptions = [];
        $this->id_tipo_envio = null;

        if (!$this->direccion_entrega_id) return;

        $direccion = DireccionEntrega::find($this->direccion_entrega_id);

        if (!$direccion || !$direccion->estado_id) return;

        // ✅ Cargar por estado
        $estado = Estado::find($direccion->estado_id);

        $this->shippingOptions = $estado
            ? $estado->tipoEnvios()->orderBy('nombre')->get()
            : [];

        // Opcional: si solo hay 1 tipo, seleccionarlo automático
        if (count($this->shippingOptions) === 1) {
            $this->id_tipo_envio = $this->shippingOptions[0]->id;
        }

        // recalcula fechas si ya hay fecha_entrega
        $this->on_Calcula_Fechas_Entrega();
    }


    public function updatedFiles()
    {
        $this->uploadedFiles = $this->buildUploadedFilesPreview(
            fn (TemporaryUploadedFile $file) => $file->getMimeType()
        );
    }

    public function procesarArchivos()
    {
        $this->uploadedFiles = $this->buildUploadedFilesPreview(
            fn (TemporaryUploadedFile $file) => $file->getClientMimeType()
        );
    }


    public function on_Calcula_Fechas_Entrega(): void
    {
        $plan = $this->deliveryDatePlanner->calculate(
            $this->fecha_entrega,
            $this->producto_id ? (int) $this->producto_id : null,
            $this->id_tipo_envio ? (int) $this->id_tipo_envio : null
        );

        if (!$plan) {
            return;
        }

        $this->fecha_embarque = $plan['fecha_embarque'];
        $this->fecha_produccion = $plan['fecha_produccion'];
        $this->mensaje_produccion = $plan['mensaje_produccion'];
    }

    public function validarFechaEntrega()
    {
        if ($this->fecha_entrega) {
            $this->fecha_entrega = $this->deliveryDatePlanner->adjustToWeekday($this->fecha_entrega);
            $this->on_Calcula_Fechas_Entrega();
        }
    }


    public function ajustarFechaSinFinesDeSemana($fecha)
    {
        return $this->deliveryDatePlanner->adjustToWeekday($fecha);
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

        $this->clientes = Cliente::where('usuario_id', $this->selectedUserId)->get();
    }

        // Función para guardar un nuevo cliente
    public function guardarCliente()
    {
        $this->validate([
            'nombre' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
            'producto_id' => 'required|exists:productos,id',

            'direccion_fiscal_id'  => 'required|exists:direcciones_fiscales,id',
            'direccion_entrega_id' => 'required|exists:direcciones_entrega,id',

            'id_tipo_envio' => 'required|exists:tipo_envio,id',

            'fecha_entrega' => 'required|date',
            'total_piezas' => 'required|integer|min:1',
            'files.*' => 'nullable|file|max:10240',
        ]);

        // Crear el cliente y asociarlo al usuario autenticado
        $cliente = Cliente::create([
            'usuario_id' => $this->selectedUserId,
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
    $this->selectedUserId = $usuarioId ? (int) $usuarioId : null;
    $this->selectedUserLookupId = $this->selectedUserId;

    // Reinicia selecciones dependientes para evitar que queden IDs del usuario anterior.
    $this->direccion_fiscal_id = null;
    $this->direccion_entrega_id = null;
    $this->id_tipo_envio = null;
    $this->shippingOptions = collect();

    $this->cargarClientes();
    $this->cargarDirecciones();
}


    public function abrirModalDireccion(string $tipo = 'entrega')
    {
        $this->tipoDireccion = in_array($tipo, ['fiscal','entrega']) ? $tipo : 'entrega';
        // reset form
        $this->formDireccion = [
            'rfc'            => '',
            'nombre_contacto'=> '',
            'nombre_empresa' => '',
            'calle'          => '',
            'pais_id'        => null,
            'estado_id'      => null,
            'ciudad_id'      => null,
            'codigo_postal'  => '',
            'telefono'       => '',
            'flag_default'   => false,
        ];
        $this->estados = [];
        $this->ciudades = [];
        $this->mostrarModalDireccion = true;
    }

    public function cerrarModalDireccion()
    {
        $this->mostrarModalDireccion = false;
    }

    // Livewire v3: updated{Property} para keys anidadas
    public function updatedFormDireccionPaisId($value)
    {
        $this->formDireccion['estado_id'] = null;
        $this->formDireccion['ciudad'] = '';
        $this->estados = $value ? Estado::where('pais_id', $value)->orderBy('nombre')->get() : [];
    }


    public function updatedFormDireccionEstadoId($value)
{
    $this->formDireccion['ciudad'] = '';
}

    public function guardarDireccion()
    {
        // Reglas comunes
        $rules = [
            'formDireccion.calle'         => 'required|string|max:255',
            'formDireccion.pais_id'       => 'required|exists:paises,id',
            'formDireccion.estado_id'     => 'required|exists:estados,id',
            'formDireccion.ciudad'        => 'required|string|max:255',
            'formDireccion.codigo_postal' => 'required|string|max:10',
            'formDireccion.flag_default'  => 'boolean',
        ];

        if ($this->tipoDireccion === 'fiscal') {
            $rules = array_merge($rules, [
                'formDireccion.rfc' => 'required|string|max:20',
            ]);
        } else {
            $rules = array_merge($rules, [
                'formDireccion.nombre_contacto' => 'required|string|max:255',
                'formDireccion.nombre_empresa'  => 'nullable|string|max:255',
                'formDireccion.telefono'        => 'nullable|string|max:20',
            ]);
        }

        $this->validate($rules);

        if (!$this->selectedUserId) {
            // En tu flujo, si permites elegir usuario, asegúrate de que esté seteado.
            // Si no, asume el usuario actual:
            $this->selectedUserId = Auth::id();
        }

        if ($this->tipoDireccion === 'fiscal') {
            // Si se marca default, limpia los demás del usuario
            if ($this->formDireccion['flag_default']) {
                DireccionFiscal::where('usuario_id', $this->selectedUserId)->update(['flag_default' => false]);
            }

            $dir = DireccionFiscal::create([
                'usuario_id'    => $this->selectedUserId,
                'rfc'           => $this->formDireccion['rfc'],
                'calle'         => $this->formDireccion['calle'],
                'pais_id'       => $this->formDireccion['pais_id'],
                'estado_id'     => $this->formDireccion['estado_id'],
                'ciudad'        => $this->formDireccion['ciudad'],   // ✅
                'codigo_postal' => $this->formDireccion['codigo_postal'],
                'flag_default'  => (bool)$this->formDireccion['flag_default'],
            ]);

            $this->cargarDirecciones();
            $this->direccion_fiscal_id = $dir->id;

        } else { // entrega
            if ($this->formDireccion['flag_default']) {
                DireccionEntrega::where('usuario_id', $this->selectedUserId)->update(['flag_default' => false]);
            }

            $dir = DireccionEntrega::create([
                'usuario_id'     => $this->selectedUserId,
                'nombre_contacto'=> $this->formDireccion['nombre_contacto'],
                'nombre_empresa' => $this->formDireccion['nombre_empresa'],
                'calle'          => $this->formDireccion['calle'],
                'pais_id'        => $this->formDireccion['pais_id'],
                'estado_id'      => $this->formDireccion['estado_id'],
                'ciudad'         => $this->formDireccion['ciudad'],  // ✅
                'codigo_postal'  => $this->formDireccion['codigo_postal'],
                'telefono'       => $this->formDireccion['telefono'],
                'flag_default'   => (bool)$this->formDireccion['flag_default'],
            ]);

            $this->cargarDirecciones();
            $this->direccion_entrega_id = $dir->id;
            $this->cargarTiposEnvio(); // refresca tipos de envío para la nueva dirección
        }

        $this->mostrarModalDireccion = false;
        session()->flash('message', 'Dirección creada correctamente.');
        // Si quieres notificación para Alpine/JS:
        // $this->dispatch('direccion-creada');
    }

    public function onPaisChange(): void
    {
        $paisId = (int) ($this->formDireccion['pais_id'] ?? 0);

        // Limpia dependencias
        $this->formDireccion['estado_id'] = null;
        $this->formDireccion['ciudad_id'] = null;

        $this->estados = $paisId
            ? Estado::where('pais_id', $paisId)->orderBy('nombre')->get()
            : collect();

        $this->ciudades = collect();
    }

    public function onEstadoChange(): void
    {
        $estadoId = (int) ($this->formDireccion['estado_id'] ?? 0);

        // Limpia ciudad al cambiar estado
        $this->formDireccion['ciudad_id'] = null;

        // $this->ciudades = $estadoId
        //     ? Ciudad::where('estado_id', $estadoId)->orderBy('nombre')->get()
        //     : collect();
    }



    public function cargarDirecciones()
    {
        Log::debug('Carga Direcciones');

        if ($this->selectedUserId) {
            $this->direccionesFiscales = DireccionFiscal::where('usuario_id', $this->selectedUserId)->get();
            $this->direccionesEntrega = DireccionEntrega::where('usuario_id', $this->selectedUserId)->get();

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
        $this->id_tipo_envio = null;
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


    public function updatedIdTipoEnvio(): void
    {
        $this->on_Calcula_Fechas_Entrega();
    }

    public function updatedProductoId(): void
    {
        $this->on_Calcula_Fechas_Entrega();
    }

    public function updatedFechaEntrega(): void
    {
        // esto mantiene tu validación de fin de semana
        $this->validarFechaEntrega();
    }

    protected function creationRules(): array
    {
        $rules = [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_produccion' => 'nullable|date',
            'fecha_embarque' => 'nullable|date',
            'fecha_entrega' => 'nullable|date',
            'categoria_id' => 'required|exists:categorias,id',
            'producto_id' => 'required|exists:productos,id',
            'id_tipo_envio'=> 'required',
            'total_piezas' => 'required|integer|min:1',
            'tallasSeleccionadas' => $this->mostrarFormularioTallas ? 'required|array|min:1' : 'nullable',
            'files.*' => 'nullable|file|max:10240',
        ];

        if ($this->mostrar_selector_armado) {
            $rules['seleccion_armado'] = 'required|in:0,1';
        }

        return $rules;
    }

    protected function validateBusinessRules(): bool
    {
        if (count($this->files) > 4) {
            $this->addError('files', 'Solo puedes subir hasta 4 archivos.');
            return false;
        }

        if ($this->mostrarFormularioTallas && $this->sumSelectedTallas() != $this->total_piezas) {
            $this->addError('total_piezas', 'La suma de las cantidades de tallas debe ser igual al total de piezas.');
            return false;
        }

        foreach ($this->caracteristicas_sel as $caracteristica) {
            if (empty($caracteristica['opciones'])) {
                $this->addError('caracteristicas_sel', "Debe seleccionar al menos una opción para '{$caracteristica['nombre']}'.");
                return false;
            }
        }

        return true;
    }

    protected function sumSelectedTallas(): int
    {
        return (int) collect(array_filter($this->tallasSeleccionadas, 'is_array'))
            ->flatMap(fn ($grupo) => array_values($grupo))
            ->sum();
    }

    protected function ensureSelectedClientUser(): void
    {
        if (!$this->selectedUserId && $this->userEsCliente((int) Auth::id())) {
            $this->selectedUserId = (int) Auth::id();
        }
    }

    protected function storePreProject(): PreProyecto
    {
        $this->seleccion_armado = $this->seleccion_armado === null || $this->seleccion_armado === ''
            ? 1
            : $this->seleccion_armado;

        return $this->preProjectCreator->create([
            'selected_user_id' => $this->selectedUserId,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'fecha_produccion' => $this->fecha_produccion,
            'fecha_embarque' => $this->fecha_embarque,
            'fecha_entrega' => $this->fecha_entrega,
            'seleccion_armado' => $this->seleccion_armado,
            'flag_requiere_proveedor' => $this->flag_requiere_proveedor,
            'categoria_id' => $this->categoria_id,
            'producto_id' => $this->producto_id,
            'caracteristicas_sel' => $this->caracteristicas_sel,
            'opciones_sel' => $this->opciones_sel,
            'direccion_entrega_id' => $this->direccion_entrega_id,
            'direccion_fiscal_id' => $this->direccion_fiscal_id,
            'id_tipo_envio' => $this->id_tipo_envio,
            'total_piezas' => $this->total_piezas,
            'detalle_tallas' => $this->mostrarFormularioTallas ? $this->normalizeTallasSelection() : null,
        ]);
    }

    protected function storeUploadedFiles(PreProyecto $preProyecto): void
    {
        $this->preProjectCreator->storeUploadedFiles($preProyecto, $this->files, $this->fileDescriptions);
    }

    protected function normalizeTallasSelection(): array
    {
        return collect($this->tallasSeleccionadas)
            ->map(fn ($tallas) => collect($tallas)->map(fn ($cantidad) => (int) $cantidad)->toArray())
            ->toArray();
    }

    protected function resetProductConfigurationState(): void
    {
        $this->tallas = collect();
        $this->tallasSeleccionadas = [];
        $this->caracteristicas_sel = [];
        $this->opciones_sel = [];
        $this->caracteristicaOpcionesDisponibles = [];
    }

    protected function hydrateTallasForSelectedProduct(): void
    {
        $configuration = $this->productConfigurationBuilder->build(
            $this->producto_id,
            (bool) $this->mostrar_selector_armado,
            $this->seleccion_armado
        );

        $this->tallas = $configuration['tallas'];
        $this->tallasSeleccionadas = $configuration['tallasSeleccionadas'];
    }

    protected function buildCaracteristicasConfiguration(): void
    {
        $this->caracteristica_id = null;
        $configuration = $this->productConfigurationBuilder->build(
            $this->producto_id,
            (bool) $this->mostrar_selector_armado,
            $this->seleccion_armado
        );

        $this->caracteristicaOpcionesDisponibles = $configuration['caracteristicaOpcionesDisponibles'];
        $this->caracteristicas_sel = $configuration['caracteristicas_sel'];
        $this->opciones_sel = $configuration['opciones_sel'];
    }

    protected function buildUploadedFilesPreview(callable $mimeResolver): array
    {
        $files = [];

        foreach ($this->files as $file) {
            if (!$file instanceof TemporaryUploadedFile) {
                continue;
            }

            $mimeType = $mimeResolver($file);
            $files[] = [
                'name' => $file->getClientOriginalName(),
                'preview' => str_starts_with($mimeType, 'image/') ? $file->temporaryUrl() : null,
            ];
        }

        return $files;
    }

    // métodos nuevos dentro de la clase
    protected function setupUsuarioSelector(): void
    {
        
    $user = Auth::user();

    $puedeTodos = $user->can('preproyectos_seleccionar_todos_usuarios');
    $subIds     = $this->currentUserSubordinateIds();

    // <- esta bandera la usará la vista
    $this->puedeBuscarUsuarios = $puedeTodos || count($subIds) > 0;

        if ($puedeTodos || count($subIds) > 0) {
            // Deja elegir; no fijes usuario por default
            $this->selectedUserId = null;
        } else {
            // Sin permiso y sin subordinados → se fija el autenticado
            $this->selectedUserId = $user->id;
        }

        $this->selectedUserLookupId = $this->selectedUserId;
    }

    /**
     * Intenta obtener los IDs de subordinados por varias fuentes
     * (relación, config o campo auxiliar usado en tu proyecto).
     */
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

        // normaliza a enteros únicos
        return array_values(array_unique(array_map('intval', $ids)));
    }

    /**
     * Refresca el arreglo $usuariosSugeridos según permiso, subordinados y query.
     * $bootstrap=true fuerza sugerir 5 subordinados al inicio (sin query).
     */
    public function refreshUsuariosSugeridos(bool $bootstrap = false): void
    {
        $user = Auth::user();
        $q = trim($this->usuarioQuery);
        $puedeTodos = $user->can('preproyectos_seleccionar_todos_usuarios');
        $subIds = $this->currentUserSubordinateIds();

        $builder = $this->baseClientesQuery();

        if (!$puedeTodos) {
            if (count($subIds) === 0) {
                // No hay de dónde sugerir
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
            // prioriza mostrar al usuario actual arriba si aparece en la lista
            ->orderByRaw('id = ? desc', [$user->id])
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'email'])
            ->toArray();

        // primer pintado: si no hay query y no salió nada, sugiere 5 subordinados
        if ($bootstrap && empty($this->usuariosSugeridos) && !$puedeTodos && count($subIds) > 0) {
            $this->usuariosSugeridos = $this->baseClientesQuery()
                ->whereIn('id', $subIds)
                ->orderBy('name')
                ->limit(5)
                ->get(['id','name','email'])
                ->toArray();
        }

    }

    // Livewire v3: cuando cambia el texto de búsqueda
    public function updatedUsuarioQuery(): void
    {
        $this->refreshUsuariosSugeridos();
    }

    // Livewire v3: cuando Alpine asigna el seleccionado
    public function updatedSelectedUserLookupId($value): void
    {
        $id = (int) $value;

        if ($id && !$this->userEsCliente($id)) {
            $this->addError('selectedUserId', 'Solo puedes seleccionar usuarios con rol tipo CLIENTE.');
            $this->selectedUserLookupId = null;
            $this->selectedUserId = null;
            $this->dispatch('usuario-cambiado', id: null);
            return;
        }

        $this->usuarioSeleccionadoCambio($id);
        $this->dispatch('usuario-cambiado', id: $id);
    }


    protected function baseClientesQuery(): Builder
    {
        return \App\Models\User::query()
            ->whereHas('roles', function ($q) {
                $q->where('tipo', 1); // 1 = CLIENTE
            });
    }

    protected function userEsCliente(int $userId): bool
    {
        return \App\Models\User::whereKey($userId)
            ->whereHas('roles', fn($q) => $q->where('tipo', 1))
            ->exists();
    }

    
}
