<?php

namespace App\Livewire\Proyectos;


use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\ArchivoProyecto;
use App\Models\Proyecto;
use App\Models\proyecto_estados;
use App\Models\Tarea;
use App\Models\Pedido; 
use App\Models\TipoEnvio; 
use App\Models\Producto; 
use Carbon\Carbon;
use App\Models\PedidoEstado;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; 
use App\Notifications\NuevaNotificacion;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class SubirDiseno extends Component
{
    use WithFileUploads;

    public $proyectoId;
    public $archivo;
    public $comentario;
    public $modalOpen = false;
    public $modalAprobar = false;
    public $modalAprobarPedido = false;
    public $modalRechazar = false;
    public $modalConfirmarMuestra = false;

    public $modalSubirArchivoDiseno = false;

    public $comentarioRechazo;
    public $estado;

    // Validacion de pedido 
    public $total;
    public $estatus;
    public $tipo;
    public $fecha_produccion;
    public $fecha_embarque;
    public $fecha_entrega;
    public $producto_id;
    public $id_tipo_envio;

    public $error_total;

    public $cobrarMuestra;

    public ArchivoProyecto|null $ultimoArchivo = null;
    public bool $archivoDuplicado = false;   // si ya la tenías, se reutiliza
    public ?string $archivoNombre = null;    // nombre original
    public ?string $archivoNombreFinal = null; // nombre con sello de tiempo que se usará en el guardado

     public bool $bloqueadoPorMuestras = false;



    // Campos del modal
    public int $cantidadMuestra = 1;
    public string $instruccionesMuestra = '';
    protected $rules = [
        'archivo' => 'required|file|max:10240',
        'comentario' => 'nullable|string|max:500',
    ];

    public function mount($proyectoId)
    {
        $this->proyectoId = $proyectoId;
        $this->cargarEstado();
         $this->actualizarBloqueoMuestras();
    }

    public function cargarEstado()
    {
        $this->estado = Proyecto::find($this->proyectoId)?->estado ?? '';
    }

    public function subir()
    {
        Log::debug('function subir');
        $this->validate($this->rulesArchivo());
        Log::debug('function validate');

        if ($this->archivoDuplicado) {
            session()->flash('error', 'Ya existe un archivo con ese nombre final en este proyecto.');
            return;
        }

        $proyecto = Proyecto::find($this->proyectoId);
        if (!$proyecto) return;

        // Aseguramos nombre final determinístico (si por alguna razón no se generó en updatedArchivo)
        $finalName = $this->archivoNombreFinal ?: $this->construirNombreFinal();

        // Guardar con nombre final
        $path = $this->archivo->storeAs('disenos', $finalName, 'public');

        $archivo = ArchivoProyecto::create([
            'proyecto_id'    => $proyecto->id,
            'nombre_archivo' => $finalName, // <-- nombre con fecha-hora-minuto
            'ruta_archivo'   => $path,
            'tipo_archivo'   => $this->archivo->getClientMimeType(),
            'usuario_id'     => Auth::id(),
            'descripcion'    => $this->comentario,
            'tipo_carga'     => 1,
            'flag_can_delete'=> 1,
        ]);

        $proyecto->estado = 'REVISION';
        $proyecto->save();

        proyecto_estados::create([
            'proyecto_id'          => $proyecto->id,
            'estado'               => 'REVISION',
            'comentario'           => $this->comentario,
            'url'                  => $archivo->ruta_archivo,
            'fecha_inicio'         => now(),
            'usuario_id'           => Auth::id(),
            'last_uploaded_file_id'=> $archivo->id,
        ]);

        Tarea::where('proyecto_id', $proyecto->id)->update(['estado' => 'EN PROCESO']);

        $this->registrarEventoEnChat("Se subió un nuevo archivo de diseño y se cambió el estado a REVISION.");

        $this->dispatch('actualizarMensajes');
        $this->dispatch('estadoActualizado');
        $this->dispatch('archivoSubido');
        $this->cargarEstado();
        $this->actualizarBloqueoMuestras();
        $this->reset(['archivo', 'comentario', 'modalOpen', 'archivoNombre', 'archivoNombreFinal', 'archivoDuplicado']);
        session()->flash('message', 'Archivo cargado correctamente.');
    }

    public function aprobarDiseno()
    {

        $this->actualizarBloqueoMuestras();
        if ($this->bloqueadoPorMuestras) {
            $this->modalRechazar = false;
            session()->flash('error', 'No puedes rechazar el diseño: existen muestras en proceso. Cierra o cancela todas las muestras (ENTREGADA o CANCELADA) para continuar.');
            return;
        }

        $this->validate([
            'comentarioRechazo' => 'required|string|min:5',
        ]);


        $proyecto = Proyecto::find($this->proyectoId);
        if (!$proyecto) return;

        $archivo = ArchivoProyecto::where('proyecto_id', $proyecto->id)->latest()->first();
        
        if ($archivo && $archivo->flag_can_delete !== 0) {
            $archivo->update(['flag_can_delete' => 0]);
        }


        $nombre = auth()->user()->name;
        
        $proyecto->estado = 'DISEÑO APROBADO';
        $proyecto->save();

        proyecto_estados::create([
            'proyecto_id' => $proyecto->id,
            'estado' => 'DISEÑO APROBADO',
            'comentario' => 'Aprobado por el cliente. '. $nombre,
            'url' => $archivo?->ruta_archivo,
            'fecha_inicio' => now(),
            'usuario_id' => Auth::id(),
            'last_uploaded_file_id' => $archivo?->id,
        ]);

        Tarea::where('proyecto_id', $proyecto->id)->update(['estado' => 'COMPLETADA']);

        $this->registrarEventoEnChat('El cliente'. $nombre .' aprobó el diseño. Estado actualizado a DISEÑO APROBADO.');

        $this->dispatch('actualizarMensajes');
        $this->dispatch('estadoActualizado');
        $this->dispatch('ActualizarTablaPedido');
        $this->cargarEstado();

        $this->modalAprobar = false;
        $this->modalAprobarPedido = true; 
        $this->dispatch('resumen_aprobacion');

        session()->flash('message', 'Diseño aprobado correctamente.');
    }

    public function rechazarDiseno()
    {

        $this->actualizarBloqueoMuestras();

        if ($this->bloqueadoPorMuestras) {
            $this->modalRechazar = false;
            session()->flash('error', 'No puedes rechazar el diseño: existen muestras en proceso. Cierra o cancela todas las muestras (ENTREGADA o CANCELADA) para continuar.');
            return;
        }

        $this->validate([
            'comentarioRechazo' => 'required|string|min:5',
        ]);

        $proyecto = Proyecto::find($this->proyectoId);
        if (!$proyecto) return;

        $archivo = ArchivoProyecto::where('proyecto_id', $proyecto->id)->latest()->first();

        if ($archivo && $archivo->flag_can_delete !== 0) {
            $archivo->update(['flag_can_delete' => 0]);
        }
        // $proyecto->estado = 'EN PROCESO';
        $proyecto->estado = 'DISEÑO RECHAZADO';
        $proyecto->save();

        Tarea::where('proyecto_id', $proyecto->id)->update(['estado' => 'RECHAZADO']);

        proyecto_estados::create([
            'proyecto_id' => $proyecto->id,
            'estado' => 'RECHAZADO',
            'comentario' => $this->comentarioRechazo,
            'url' => $archivo?->ruta_archivo,
            'fecha_inicio' => now(),
            'usuario_id' => Auth::id(),
            'last_uploaded_file_id' => $archivo?->id,
        ]);

        $this->registrarEventoEnChat('El cliente rechazó el diseño. Comentario: ' . $this->comentarioRechazo);

        $this->dispatch('estadoActualizado');
        $this->dispatch('actualizarMensajes');
        $this->cargarEstado();

        $this->modalRechazar = false;
        $this->comentarioRechazo = '';
        session()->flash('message', 'Diseño rechazado correctamente.');
    }

    protected function registrarEventoEnChat($mensaje)
    {
        $proyecto = Proyecto::find($this->proyectoId);
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

    protected function rulesMuestra(): array
        {
                return [
                    'cantidadMuestra'      => 'required|integer|min:1|max:10',
                    'instruccionesMuestra' => 'nullable|string|max:500',
                ];
            }

    public function crearMuestraDesdeDiseno()
    {
        $proyecto = Proyecto::find($this->proyectoId);
    
        $this->validate($this->rulesMuestra());

        
        if (!$proyecto) {
            session()->flash('error', 'No se encontró el proyecto.');
            return;
        }
    
        $archivo = ArchivoProyecto::where('proyecto_id', $proyecto->id)->latest()->first();

        
    
        if (!$archivo) {
            session()->flash('error', 'No puedes crear una muestra sin haber cargado al menos un archivo de diseño.');
            return;
        }
    
        // Validar si ya existe una muestra para este archivo y proyecto
        $existe = Pedido::where('proyecto_id', $proyecto->id)
            ->where('last_uploaded_file_id', $archivo->id)
            ->where('tipo', 'MUESTRA')
            ->exists();
    
        if ($existe) {
            session()->flash('error', 'Ya existe una muestra registrada para este diseño.');
            $this->modalConfirmarMuestra = false;
            return;
        }




        $muestrasPrevias = Pedido::where('proyecto_id', $proyecto->id)
            ->where('tipo', 'MUESTRA')
            // ->where('estatus', '!=', 'CANCELADA') // <-- descomenta si NO quieres contar canceladas
            ->count();

        // Si ya hay 2 o más, esta (la próxima) se cobra
        $cobrarMuestra = $muestrasPrevias >= 2;

        $totalMuestras = Pedido::where('proyecto_id', $proyecto->id)
        ->where('tipo', 'MUESTRA')
        ->count();

        $estatusMuestra = $totalMuestras > 0 ? 'PENDIENTE' : 'SOLICITADA';


        Pedido::crearMuestra($proyecto->id, [
            'cliente_id' => $proyecto->usuario_id,
            'direccion_fiscal_id' => null,
            'direccion_entrega_id' => null,
            'tipo' => 'MUESTRA',
            'estado' => 'POR APROBAR',
            'total' => $this->cantidadMuestra,
            'instrucciones_muestra' => $this->instruccionesMuestra,
            'fecha_produccion' => null,
            'fecha_embarque' => null,
            'fecha_entrega' => null,
            'id_tipo_envio' => null,
            'cobrar_muestra' => $cobrarMuestra, 
            'estatusMuestra' => $estatusMuestra
        ]);


        if ($archivo && $archivo->flag_can_delete !== 0) {
            $archivo->update(['flag_can_delete' => 0]);
        }


        $this->modalConfirmarMuestra = false;
        session()->flash('message', "Muestra creada correctamente con estatus {$estatusMuestra}.");
        $this->dispatch('ActualizarTablaMuestra');

        // Evento de log al log de pedidos  

        // ..
    
        $this->modalConfirmarMuestra = false;
        session()->flash('message', 'Muestra creada correctamente.');
        // $this->dispatch('muestraCreada');
        $this->dispatch('ActualizarTablaMuestra');

        $this->actualizarBloqueoMuestras();
        
    }

    public function aprobarUltimoPedido()
    {

        Log::debug('aprobarUltimoPedido');
        $pedido = Pedido::where('proyecto_id', $this->proyectoId)
            ->where('estado', 'POR APROBAR')
            ->where('tipo', 'PEDIDO')
            ->latest('created_at')
            ->first();

        Log::debug('Busqueda de pedido');
    
        if (!$pedido) {
            session()->flash('error', 'No se encontró un pedido en estado POR APROBAR.');
            $this->modalAprobarPedido = false;
            return;
        }

        Log::debug('No se encontro pedido');
    
        // Asignación de datos
        $this->total = $pedido->total;
        $this->estatus = $pedido->estatus ?? 'PENDIENTE';
        $this->tipo = $pedido->tipo ?? 'PEDIDO';
        $this->estado = 'APROBADO';
        $this->fecha_produccion = $pedido->fecha_produccion;
        $this->fecha_embarque = $pedido->fecha_embarque;
        $this->fecha_entrega = $pedido->fecha_entrega;
        $this->producto_id = $pedido->producto_id;
        $this->id_tipo_envio = $pedido->id_tipo_envio;

        Log::debug('Asignacion de datos');
        Log::debug('estatus',['data' =>  $this->estatus]);
        Log::debug('tipo',['data' => $this->tipo]);
        Log::debug('estado', ['data' => $this->estado]);
        Log::debug('fecha_produccion', ['data' =>  $this->fecha_produccion]);
        Log::debug('fecha_embarque', ['data' =>  $this->fecha_embarque]);
        Log::debug('fecha_entrega', ['data' =>  $this->fecha_entrega]);
        // Validación
        Log::debug('Pre validacion');
        $this->validate($this->rulesPedido());
        Log::debug('Validacion');
        // Calcular fechas si es necesario
        if (!$this->fecha_produccion || !$this->fecha_embarque) {
            $this->on_Calcula_Fechas_Entrega();
        }
        Log::debug('Fechas');
        // ¿Se requiere autorización? fecha de producción es anterior a hoy
        $fechaProduccion = Carbon::parse($this->fecha_produccion);
        $ahora = Carbon::now();
        Log::debug('Fechas');

        if ($fechaProduccion->lt($ahora)) {
            // Cierra modal actual
            $this->modalAprobarPedido = false;
    
            // Emitir evento con ID del pedido
            $this->dispatch('abrirModalEdicion', pedidoId: $pedido->id);
            return;
        }
    
        // Si no se requiere autorización, actualizar normalmente

        Log::debug('Update pedido');
        $pedido->update([
            'estado' => 'APROBADO',
            'fecha_produccion' => $this->fecha_produccion,
            'fecha_embarque' => $this->fecha_embarque,
            'fecha_entrega' => $this->fecha_entrega,
        ]);

        // actualizar los valores de la tabla
        $this->dispatch('ActualizarTablaPedido');
        
        $this->modalAprobarPedido = false;
        session()->flash('message', 'Pedido aprobado y programado correctamente.');
        $this->dispatch('pedidoAprobado');
    }
    
    protected function rulesArchivo()
    {
            return [
                'archivo' => 'required|file|max:10240|mimes:jpg,jpeg,png,webp,svg,ai,psd,pdf,zip',
                'comentario' => 'nullable|string|max:500',
            ];
    }

    protected function rulesPedido()
    {
        return [
            'estatus' => 'required|string',
            'tipo' => 'required|in:PEDIDO,MUESTRA',
            'estado' => 'required|in:POR APROBAR,APROBADO,ENTREGADO,RECHAZADO,ARCHIVADO',
            'fecha_produccion' => 'nullable|date',
            'fecha_embarque' => 'nullable|date',
            'fecha_entrega' => 'nullable|date',
        ];
    }

    public function on_Calcula_Fechas_Entrega()
    {
        if ($this->fecha_entrega) {
            // Registrar la fecha ingresada
            Log::debug('Fecha de entrega ingresada', ['fecha_entrega' => $this->fecha_entrega]);
    
            // Convertir la fecha ingresada a un objeto Carbon
            $fecha_entrega = Carbon::parse($this->fecha_entrega);
            $ahora = Carbon::now();
    
            // Definir los días requeridos por defecto
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
                $producto = Producto::find($this->producto_id);
    
                if ($producto) {
                    $dias_produccion_producto = $producto->dias_produccion;
                    Log::debug('Días de producción obtenidos de la BD', ['dias_produccion' => $dias_produccion_producto]);
                } else {
                    Log::warning('No se encontró el producto en la BD', ['producto_id' => $this->producto_id]);
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
    
            // Evaluamos si la fecha de producción está en tiempo de producción
            if ($fecha_produccion->lt($ahora)) {
                Log::warning('Este proyecto requiere autorización adicional para producción.');
            }
    
            // Log para depuración
            Log::debug('Fechas calculadas', [
                'fecha_produccion' => $this->fecha_produccion,
                'fecha_embarque' => $this->fecha_embarque,
            ]);
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

    public function subirArchivoDiseno()
    {
        $this->validate([
            'archivo'   => 'required|file|max:10240',
            'comentario'=> 'nullable|string|max:500',
        ]);

        if ($this->archivoDuplicado) {
            session()->flash('error', 'Ya existe un archivo con ese nombre final en este proyecto.');
            return;
        }

        $proyecto = Proyecto::find($this->proyectoId);
        if (!$proyecto) return;

        $finalName = $this->archivoNombreFinal ?: $this->construirNombreFinal();
        $path = $this->archivo->storeAs('disenos', $finalName, 'public');

        ArchivoProyecto::create([
            'proyecto_id'    => $proyecto->id,
            'nombre_archivo' => $finalName, // <-- nombre con fecha-hora-minuto
            'ruta_archivo'   => $path,
            'tipo_archivo'   => $this->archivo->getClientMimeType(),
            'usuario_id'     => Auth::id(),
            'descripcion'    => $this->comentario,
            'tipo_carga'     => 1,
            'flag_can_delete'=> 1,
        ]);

        $archivo = ArchivoProyecto::where('proyecto_id', $proyecto->id)->latest('id')->first();
        $totalArchivos = ArchivoProyecto::where('proyecto_id', $proyecto->id)->count();

        $comentarioEstado = $totalArchivos > 1
            ? 'El cliente ha actualizado el archivo de arte del proyecto'
            : 'El cliente ha cargado el primer arte del proyecto';

        $AuxEstado = $totalArchivos > 1 ? 'ARTE CARGADO' : 'PRIMER ARTE CARGADO';

        proyecto_estados::create([
            'proyecto_id'          => $proyecto->id,
            'estado'               => $AuxEstado,
            'comentario'           => $comentarioEstado,
            'url'                  => $archivo?->ruta_archivo,
            'fecha_inicio'         => now(),
            'usuario_id'           => Auth::id(),
            'last_uploaded_file_id'=> $archivo?->id,
        ]);

        $this->reset([
            'archivo', 'comentario', 'modalSubirArchivoDiseno',
            'archivoNombre', 'archivoNombreFinal', 'archivoDuplicado'
        ]);
        $this->dispatch('archivoSubido');
        session()->flash('message', 'Archivo de diseño cargado correctamente.');
    }


    public function updatedModalConfirmarMuestra(bool $open)
    {
        if ($open) {
            $this->ultimoArchivo = ArchivoProyecto::where('proyecto_id', $this->proyectoId)
                ->where('tipo_carga', 1)
                ->latest('id')
                ->first();
        }
    }

    private function construirNombreFinal(): ?string
    {
        if (!$this->archivo) {
            return null;
        }

        $timestamp = now()->format('Ymd_Hi'); // 20250918_1142
        $original  = $this->archivo->getClientOriginalName();
        $base      = pathinfo($original, PATHINFO_FILENAME);
        $ext       = $this->archivo->getClientOriginalExtension();

        // slug del nombre base (sin extensión), con guion bajo
        $baseSlug  = Str::slug($base, '_');
        // limitar un poco el largo del base para rutas amigables
        $baseSlug  = Str::limit($baseSlug, 80, '');

        return $ext
            ? "{$baseSlug}_{$timestamp}.{$ext}"
            : "{$baseSlug}_{$timestamp}";
    }



    public function updatedArchivo(): void
    {
        $this->archivoDuplicado = false;
        $this->archivoNombre    = null;
        $this->archivoNombreFinal = null;

        if (!$this->archivo || !$this->proyectoId) {
            return;
        }

        $this->archivoNombre      = $this->archivo->getClientOriginalName();
        $this->archivoNombreFinal = $this->construirNombreFinal();

        // Si quieres bloquear por duplicado exacto del nombre FINAL (con timestamp),
        // esto casi nunca ocurrirá. Lo dejamos por si hay almacenamiento repetido en el mismo minuto.
        $this->archivoDuplicado = \App\Models\ArchivoProyecto::where('proyecto_id', $this->proyectoId)
            ->where('nombre_archivo', $this->archivoNombreFinal)
            ->exists();
    }


    public function notificarEstatus(): void
    {
        try {
            // (Opcional) refuerzo de autorización además del @can en Blade
            // if (!auth()->user()?->can('notificaralclienteproyecto')) {
            //     session()->flash('error', 'No tienes permisos para notificar al cliente.');
            //     return;
            // }

            $proyecto = Proyecto::with(['user'])->find($this->proyectoId);
            if (!$proyecto) {
                session()->flash('error', 'Proyecto no encontrado.');
                return;
            }

            // 1) Notificar al usuario creador del proyecto
            $destinatario = $proyecto->user; // <- relación correcta del modelo
            if ($destinatario) {
                $liga = route('proyecto.show', $proyecto->id);
                $mensaje = "Tu proyecto #{$proyecto->id} cambió de estatus a {$proyecto->estado}. Revisa las novedades del diseño.";
                $destinatario->notify(new NuevaNotificacion($mensaje, $liga));
            } else {
                Log::warning('Proyecto sin usuario asociado', ['proyecto_id' => $proyecto->id]);
            }

            // 2) Proteger archivos: poner en 0 todos los que estén en 1
            \App\Models\ArchivoProyecto::where('proyecto_id', $proyecto->id)
                ->where('flag_can_delete', 1)
                ->update(['flag_can_delete' => 0]);

            // 3) Registrar en chat/historial (tu helper existente)
            $this->registrarEventoEnChat('Se notificó al cliente sobre el estatus del proyecto y se protegieron los archivos (flag_can_delete = 0).');

            // 4) Refrescar UI
            $this->dispatch('actualizarMensajes');
            $this->dispatch('estadoActualizado');

            session()->flash('message', 'Notificación enviada y archivos protegidos correctamente.');
        } catch (\Throwable $e) {
            Log::error('Error al notificar estatus', ['error' => $e->getMessage()]);
            session()->flash('error', 'Ocurrió un error al notificar el estatus.');
        }
    }


    private function hasMuestrasActivas(int $proyectoId): bool
    {
        // Detecta la columna disponible
        $col = Schema::hasColumn('pedido', 'estatus_muestra')
            ? 'estatus_muestra'
            : (Schema::hasColumn('pedido', 'estatus')
                ? 'estatus'
                : 'estado');

        return Pedido::where('proyecto_id', $proyectoId)
            ->where('tipo', 'MUESTRA')
            ->where(function ($q) use ($col) {
                // Activa si no está entregada o cancelada.
                // Considera NULL como "aún activa" (no cerrada).
                $q->whereNull($col)
                ->orWhereNotIn($col, ['ENTREGADA', 'CANCELADA']);
            })
            ->exists();
    }

    private function actualizarBloqueoMuestras(): void
    {
        $this->bloqueadoPorMuestras = $this->hasMuestrasActivas($this->proyectoId);
    }



    public function render()
    {
        return view('livewire.proyectos.subir-diseno');
    }
}