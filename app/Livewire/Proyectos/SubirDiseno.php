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

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\log;


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


    protected $rules = [
        'archivo' => 'required|file|max:10240',
        'comentario' => 'nullable|string|max:500',
    ];

    public function mount($proyectoId)
    {
        $this->proyectoId = $proyectoId;
        $this->cargarEstado();
    }

    public function cargarEstado()
    {
        $this->estado = Proyecto::find($this->proyectoId)?->estado ?? '';
    }

    public function subir()
    {
        Log::debug('function subir');
        Log::debug('function pre validate');
        $this->validate($this->rulesArchivo());
        Log::debug('function validate');

        $proyecto = Proyecto::find($this->proyectoId);
        if (!$proyecto) return;

        $path = $this->archivo->store('disenos', 'public');

        $archivo = ArchivoProyecto::create([
            'proyecto_id' => $proyecto->id,
            'nombre_archivo' => $this->archivo->getClientOriginalName(),
            'ruta_archivo' => $path,
            'tipo_archivo' => $this->archivo->getClientMimeType(),
            'usuario_id' => Auth::id(),
            'descripcion' => $this->comentario,
        ]);

        $proyecto->estado = 'REVISION';
        $proyecto->save();

        proyecto_estados::create([
            'proyecto_id' => $proyecto->id,
            'estado' => 'REVISION',
            'comentario' => $this->comentario,
            'url' => $archivo->ruta_archivo,
            'fecha_inicio' => now(),
            'usuario_id' => Auth::id(),
            'last_uploaded_file_id' => $archivo->id,
        ]);

        Tarea::where('proyecto_id', $proyecto->id)->update(['estado' => 'EN PROCESO']);

        $this->registrarEventoEnChat("Se subió un nuevo archivo de diseño y se cambió el estado a REVISION.");

        $this->dispatch('estadoActualizado');
        $this->cargarEstado();

        $this->reset(['archivo', 'comentario', 'modalOpen']);
        session()->flash('message', 'Archivo subido correctamente.');
    }

    public function aprobarDiseno()
    {
        $proyecto = Proyecto::find($this->proyectoId);
        if (!$proyecto) return;

        $archivo = ArchivoProyecto::where('proyecto_id', $proyecto->id)->latest()->first();

        $proyecto->estado = 'DISEÑO APROBADO';
        $proyecto->save();

        proyecto_estados::create([
            'proyecto_id' => $proyecto->id,
            'estado' => 'DISEÑO APROBADO',
            'comentario' => 'Aprobado por el cliente',
            'url' => $archivo?->ruta_archivo,
            'fecha_inicio' => now(),
            'usuario_id' => Auth::id(),
            'last_uploaded_file_id' => $archivo?->id,
        ]);

        Tarea::where('proyecto_id', $proyecto->id)->update(['estado' => 'COMPLETADA']);

        $this->registrarEventoEnChat('El cliente aprobó el diseño. Estado actualizado a DISEÑO APROBADO.');

        $this->dispatch('estadoActualizado');
        $this->dispatch('ActualizarTablaPedido');
        $this->cargarEstado();

        $this->modalAprobar = false;
        $this->modalAprobarPedido = true; 

        session()->flash('message', 'Diseño aprobado correctamente.');
    }

    public function rechazarDiseno()
    {
        $this->validate([
            'comentarioRechazo' => 'required|string|min:5',
        ]);

        $proyecto = Proyecto::find($this->proyectoId);
        if (!$proyecto) return;

        $archivo = ArchivoProyecto::where('proyecto_id', $proyecto->id)->latest()->first();

        $proyecto->estado = 'EN PROCESO';
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

    public function crearMuestraDesdeDiseno()
    {
        $proyecto = Proyecto::find($this->proyectoId);
    
        if (!$proyecto) {
            session()->flash('error', 'No se encontró el proyecto.');
            return;
        }
    
        $archivo = ArchivoProyecto::where('proyecto_id', $proyecto->id)->latest()->first();
    
        if (!$archivo) {
            session()->flash('error', 'No puedes crear una muestra sin haber subido al menos un archivo de diseño.');
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
    
        Pedido::crearMuestra($proyecto->id, [
            'cliente_id' => $proyecto->usuario_id,
            'direccion_fiscal_id' => null,
            'direccion_entrega_id' => null,
            'tipo' => 'MUESTRA',
            'estado' => 'POR PROGRAMAR',
            'total' => 0,
            'fecha_produccion' => null,
            'fecha_embarque' => null,
            'fecha_entrega' => null,
            'id_tipo_envio' => null,
        ]);
    
        $this->modalConfirmarMuestra = false;
        session()->flash('message', 'Muestra creada correctamente.');
        $this->dispatch('muestraCreada');
    }

    public function aprobarUltimoPedido()
    {
        $pedido = Pedido::where('proyecto_id', $this->proyectoId)
            ->where('estado', 'POR PROGRAMAR')
            ->where('tipo', 'PEDIDO')
            ->latest('created_at')
            ->first();
    
        if (!$pedido) {
            session()->flash('error', 'No se encontró un pedido en estado POR PROGRAMAR.');
            $this->modalAprobarPedido = false;
            return;
        }
    
        // Asignación de datos
        $this->total = $pedido->total;
        $this->estatus = $pedido->estatus ?? 'PENDIENTE';
        $this->tipo = $pedido->tipo ?? 'PEDIDO';
        $this->estado = 'PROGRAMADO';
        $this->fecha_produccion = $pedido->fecha_produccion;
        $this->fecha_embarque = $pedido->fecha_embarque;
        $this->fecha_entrega = $pedido->fecha_entrega;
        $this->producto_id = $pedido->producto_id;
        $this->id_tipo_envio = $pedido->id_tipo_envio;
    
        // Validación
       
        $this->validate($this->rulesPedido());
    
        // Calcular fechas si es necesario
        if (!$this->fecha_produccion || !$this->fecha_embarque) {
            $this->on_Calcula_Fechas_Entrega();
        }
    
        // ¿Se requiere autorización? fecha de producción es anterior a hoy
        $fechaProduccion = Carbon::parse($this->fecha_produccion);
        $ahora = Carbon::now();
    
        if ($fechaProduccion->lt($ahora)) {
            // Cierra modal actual
            $this->modalAprobarPedido = false;
    
            // Emitir evento con ID del pedido
            $this->dispatch('abrirModalEdicion', pedidoId: $pedido->id);
            return;
        }
    
        // Si no se requiere autorización, actualizar normalmente
        $pedido->update([
            'estado' => 'PROGRAMADO',
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
            'archivo' => 'required|file|max:10240',
            'comentario' => 'nullable|string|max:500',
        ];
    }



    protected function rulesPedido()
    {
        return [
            'estatus' => 'required|string',
            'tipo' => 'required|in:PEDIDO,MUESTRA',
            'estado' => 'required|in:POR PROGRAMAR,PROGRAMADO,IMPRESIÓN,PRODUCCIÓN,COSTURA,ENTREGA,FACTURACIÓN,COMPLETADO,RECHAZADO',
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


    public function render()
    {
        return view('livewire.proyectos.subir-diseno');
    }
}