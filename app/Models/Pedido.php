<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Models\ArchivoProyecto;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\ArchivoPedido;
use App\Models\TareaProduccion;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pedido extends Model
{
    use HasFactory;
    protected $table = 'pedido'; // Nombre correcto de la tabla
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'id',
        'proyecto_id',
        'producto_id',
        'user_id',
        'cliente_id',
        'fecha_creacion',
        'total',
        'total_minutos',
        'total_pasos',
        'resumen_tiempos',
        'estatus',
        'direccion_fiscal_id',
        'direccion_fiscal',
        'direccion_entrega_id',
        'direccion_entrega',
        'tipo',
        'estatus_entrega_muestra',
        'estatus_muestra',
        'estado',
        'estado_id',
        'estado_produccion',
        'fecha_produccion',
        'fecha_embarque',
        'fecha_entrega',
        'id_tipo_envio',
        'descripcion_pedido',
        'instrucciones_muestra',
        'flag_facturacion',
        'url',
        'last_uploaded_file_id',
        'flag_aprobar_sin_fechas',
        'flag_solicitud_aprobar_sin_fechas',
        'flag_solicitud_aprobar_sin_fechas',
        'ind_activo',

        'estatus_proveedor',
        'proveedor_visto_at',
        'proveedor_visto_por',
        'nota_proveedor',
    ];

    protected $casts = [
        'fecha_produccion' => 'date',     // Carbon|null
        'fecha_embarque'   => 'date',
        'fecha_entrega'    => 'date',
        'proveedor_visto_at' => 'datetime',
    ];
    public function tareasProduccion()
    {
        return $this->belongsToMany(TareaProduccion::class, 'pedido_tarea', 'pedido_id', 'tarea_produccion_id');
    }
    /**
     * Relación con la tabla de clientes.
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
    /**
     * Relación con el modelo Proyecto.
     */
    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }
    /**
     * Relación con el modelo Producto.
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
    // Relacion con el modelo de pedidoCaracteristica
    public function pedidoCaracteristicas()
    {
        return $this->hasMany(PedidoCaracteristica::class, 'pedido_id');
    }
    public function pedidoOpciones()
    {
        return $this->hasMany(PedidoOpcion::class, 'pedido_id');
    }
    // App\Models\Pedido.php
    public function estados()
    {
        return $this->hasMany(PedidoEstado::class, 'pedido_id');
    }
    public function pedidoTallas()
    {
        return $this->hasMany(PedidoTalla::class, 'pedido_id');
    }
    // Relación con TipoEnvio
    public function tipoEnvio()
    {
        return $this->belongsTo(TipoEnvio::class, 'id_tipo_envio');
    }
    // app/Models/Pedido.php (extracto)
    public function estadoPedido()
    {
        return $this->belongsTo(\App\Models\EstadoPedido::class, 'estado_id');
    }
    /**
     * (Opcional) Virtual: $pedido->estado regresa el nombre del catálogo.
     * Útil si aún tienes vistas/código que leen "estado".
     */
    public function getEstadoAttribute(): ?string
    {
        return $this->estadoPedido->nombre ?? null;
    }  
    public function tipoPedido()
    {
        return $this->belongsTo(TipoPedido::class, 'tipo_id');
    }  
    public static function crearDesdeProyecto($proyectoId, $data)
    {

        Log::debug('ON crearDesdeProyecto');
        // Buscar el proyecto
        $proyecto = Proyecto::findOrFail($proyectoId);
        Log::debug('Proyecto cargado:', ['proyecto' => $proyecto]);
    
        // Decodificar producto_sel (si es JSON)
        $producto = is_string($proyecto->producto_sel) 
            ? json_decode($proyecto->producto_sel, true) 
            : $proyecto->producto_sel;
    
        Log::debug('Producto decodificado:', ['producto' => $producto]);
    
        // Verificar si el producto tiene ID
        if (!isset($producto['id'])) {
            throw new \Exception("Error: No se encontró un producto válido en el proyecto.");
        }
    
        // Obtener cliente_id desde el proyecto o establecer un valor por defecto
        $clienteId = $data['cliente_id'] ?? $proyecto->usuario_id ?? null;
    
        // Si cliente_id sigue siendo null, lanzar un error
        if (!$clienteId) {
            throw new \Exception("Error: No se encontró un cliente válido para el pedido.");
        }


                        
    
        // Crear pedido
        $pedido = self::create([
            'proyecto_id' => $proyecto->id,
            'producto_id' => $producto['id'],
            'user_id' => $proyecto->usuario_id, // Ahora tiene un valor asegurado
            'cliente_id' => $clienteId, // Ahora tiene un valor asegurado
            'fecha_creacion' => now(),
            'total' => $data['total'] ?? 0,
            'estatus' => $data['estatus'] ?? 'PENDIENTE',
            'direccion_fiscal_id' => $data['direccion_fiscal_id'] ?? null,
            'direccion_fiscal' => $data['direccion_fiscal'] ?? null,
            'direccion_entrega_id' => $data['direccion_entrega_id'] ?? null,
            'direccion_entrega' => $data['direccion_entrega'] ?? null,
            'tipo' => $data['tipo'] ?? 'PEDIDO',
            'estado' => $data['estado'] ?? 'POR PROGRAMAR',
            'estado_id' => $data['estado_id'] ?? 1,
            'fecha_produccion' => $data['fecha_produccion'] ?? null,
            'fecha_embarque' => $data['fecha_embarque'] ?? null,
            'fecha_entrega' => $data['fecha_entrega'] ?? null,
            'id_tipo_envio' => $data['id_tipo_envio'] ?? null,
            'url' =>null,
            'last_uploaded_file_id'=>null
        ]);


        // Guardar tallas si están disponibles
        if (!empty($data['cantidades_tallas'])) {
            foreach ($data['cantidades_tallas'] as $tallaId => $cantidad) {
                if ($cantidad > 0) {
                    PedidoTalla::create([
                        'pedido_id' => $pedido->id,
                        'talla_id' => $tallaId,
                        'cantidad' => $cantidad,
                    ]);
                }
            }
        }

    
        return $pedido;
    }
    public function archivo()
    {
        return $this->belongsTo(ArchivoProyecto::class, 'last_uploaded_file_id');
    }
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function ordenesProduccion()
    {
        return $this->belongsToMany(
            \App\Models\OrdenProduccion::class,
            'pedido_orden_produccion',
            'pedido_id',
            'orden_produccion_id'
        );
    }
    public function getUltimoEstatusOrdenProduccionAttribute()
    {
        $orden = $this->ordenesProduccion()->orderByDesc('created_at')->first();
        if ($orden) {
            return ($orden->tipo ?? 'N/D') . ':' . ($orden->estado ?? 'N/D');
        }
        return null;
    }   
    public static function crearMuestra($proyectoId, $data)
    {
        Log::debug('ON crearMuestra');

        $proyecto = Proyecto::findOrFail($proyectoId);

        $producto = is_string($proyecto->producto_sel)
            ? json_decode($proyecto->producto_sel, true)
            : $proyecto->producto_sel;

        if (!isset($producto['id'])) {
            throw new \Exception("Error: No se encontró un producto válido en el proyecto.");
        }

        $clienteId = $data['cliente_id'] ?? $proyecto->usuario_id ?? null;
        if (!$clienteId) {
            throw new \Exception("Error: No se encontró un cliente válido para el pedido.");
        }

        // Tomar el ÚLTIMO archivo de DISEÑO (tipo_carga = 1), como en la vista
        $ultimoArchivo = ArchivoProyecto::where('proyecto_id', $proyectoId)
            ->where('tipo_carga', 1)
            ->latest('id')
            ->first();

        $url = $ultimoArchivo ? Storage::disk('public')->url($ultimoArchivo->ruta_archivo) : null;
        $lastFileId = $ultimoArchivo?->id;

        // (Opcional) Evitar duplicados de muestra por archivo
        if (!empty($lastFileId)) {
            $existe = self::where('proyecto_id', $proyecto->id)
                ->where('last_uploaded_file_id', $lastFileId)
                ->where('tipo', 'MUESTRA')
                ->exists();

            if ($existe) {
                throw new \Exception(message: 'Ya existe una muestra registrada para este diseño.');
            }
        }

        // Crear pedido MUESTRA (¡ahora sí guarda instrucciones_muestra!)
        $pedido = self::create([
            'proyecto_id'           => $proyecto->id,
            'producto_id'           => $producto['id'],
            'user_id'               => $proyecto->usuario_id,
            'cliente_id'            => $clienteId,
            'fecha_creacion'        => now(),
            'total'                 => $data['total'] ?? 0, // aquí usas cantidadMuestra
            'estatus'               => $data['estatus'] ?? 'PENDIENTE',
            'direccion_fiscal_id'   => $data['direccion_fiscal_id'] ?? null,
            'direccion_fiscal'      => $data['direccion_fiscal'] ?? null,
            'direccion_entrega_id'  => $data['direccion_entrega_id'] ?? null,
            'direccion_entrega'     => $data['direccion_entrega'] ?? null,
            'tipo'                  => 'MUESTRA',
            'estatus_muestra'       => $data ['estatusMuestra'],
            'estado'                => $data['estado'] ?? 'POR PROGRAMAR', // tú mandas 'POR APROBAR'
            'fecha_produccion'      => $data['fecha_produccion'] ?? null,
            'fecha_embarque'        => $data['fecha_embarque'] ?? null,
            'fecha_entrega'         => $data['fecha_entrega'] ?? null,
            'id_tipo_envio'         => $data['id_tipo_envio'] ?? null, // por si luego lo ocupas
            'descripcion_pedido'    => $data['descripcion_pedido'] ?? null, // (opcional)
            'instrucciones_muestra' => $data['instrucciones_muestra'] ?? null, //
            'url'                   => $url,
            'last_uploaded_file_id' => $lastFileId,
            'flag_facturacion'      => (int) ($data['flag_facturacion'] ?? 0), // 0 = no cobra, 1 = sí cobra

             'estatus_proveedor' => $data['estatus_proveedor'] ?? 'PENDIENTE',
        ]);

        PedidoEstado::create([
                'pedido_id'    => $pedido->id,
                'proyecto_id'  => $pedido->proyecto_id,
                'usuario_id'   => Auth::id(),
                'estado'       => $data ['estatusMuestra'],
                'fecha_inicio' => now(), // opcional, si lo usas en tu flujo
        ]);

        if (!empty($data['cantidades_tallas'])) {
            foreach ($data['cantidades_tallas'] as $tallaId => $cantidad) {
                if ($cantidad > 0) {
                    PedidoTalla::create([
                        'pedido_id' => $pedido->id,
                        'talla_id'  => $tallaId,
                        'cantidad'  => $cantidad,
                    ]);
                }
            }
        }

        return $pedido;
    }
    public static function calculaTiemposTotalesPorId($pedidoId)
    {
        $pedido = self::with(['pedidoOpciones.opcion'])->findOrFail($pedidoId);
    
        $opciones = $pedido->pedidoOpciones
            ->map(fn($po) => $po->opcion)
            ->filter();
    
        $total_pasos = $opciones->sum(fn($op) => $op->pasos) * $pedido->total;
    
        // convertir minutoPaso de HH:MM:SS a minutos
        $total_minutos = $opciones->sum(function ($op) {
            if (!$op->minutoPaso) return 0;
    
            $tiempo = Carbon::createFromFormat('H:i:s', $op->minutoPaso);
            return $tiempo->hour * 60 + $tiempo->minute + round($tiempo->second / 60, 2);
        }) * $pedido->total;
    
        return [
            'total_pasos' => (int) $total_pasos,
            'total_minutos' => round($total_minutos, 2),
            'opciones' => $opciones->map(fn($op) => [
                'id' => $op->id,
                'nombre' => $op->nombre,
                'pasos' => $op->pasos,
                'minutoPaso' => $op->minutoPaso,
            ])->toArray(),
        ];
    }
    public static function asignaTotalesPasoTiempo($pedidoId)
    {
        $pedido = self::findOrFail($pedidoId);

        $resultados = self::calculaTiemposTotalesPorId($pedidoId);

        $pedido->update([
            'total_pasos' => $resultados['total_pasos'],
            'total_minutos' => $resultados['total_minutos'],
            'resumen_tiempos' => json_encode($resultados, JSON_UNESCAPED_UNICODE),
        ]);
    

        return $resultados;
    }
    // App\Models\Pedido.php
    public function getTallasAgrupadasAttribute()
    {
        if (!$this->relationLoaded('pedidoTallas')) {
            $this->load(['pedidoTallas.talla', 'pedidoTallas.grupoTalla']);
        }

        return $this->pedidoTallas
            ->groupBy('grupo_talla_id')
            ->map(function ($tallas, $grupoTallaId) {
                return [
                    'grupo_nombre' => $tallas->first()->grupoTalla->nombre ?? 'Sin Grupo',
                    'tallas' => $tallas->map(function ($talla) {
                        return [
                            'nombre' => $talla->talla->nombre ?? 'N/A',
                            'cantidad' => $talla->cantidad ?? 0,
                        ];
                    }),
                ];
            });
    }
    public static function combinarTallasDePedidos($pedidos)
    {
        $tallasCombinadas = collect();
    
        foreach ($pedidos as $pedido) {
            if (!$pedido->relationLoaded('pedidoTallas')) {
                $pedido->load(['pedidoTallas.talla', 'pedidoTallas.grupoTalla']);
            }
    
            foreach ($pedido->pedidoTallas as $pedidoTalla) {
                $grupoId = $pedidoTalla->grupo_talla_id;
                $tallaId = $pedidoTalla->talla_id;
                $clave = $grupoId . '_' . $tallaId;
    
                $tallasCombinadas[$clave] = [
                    'grupo_talla_id' => $grupoId,
                    'grupo_nombre' => $pedidoTalla->grupoTalla->nombre ?? 'Sin Grupo',
                    'talla_id' => $tallaId,
                    'talla_nombre' => $pedidoTalla->talla->nombre ?? 'N/A',
                    'cantidad' => ($tallasCombinadas[$clave]['cantidad'] ?? 0) + ($pedidoTalla->cantidad ?? 0),
                ];
            }
        }
    
        // Agrupar por grupo_nombre para que sea más fácil mostrarlo en la vista
        return $tallasCombinadas
            ->groupBy('grupo_nombre')
            ->map(function ($tallas, $grupoNombre) {
                return [
                    'grupo_nombre' => $grupoNombre,
                    'tallas' => $tallas->map(fn($item) => [
                        'nombre' => $item['talla_nombre'],
                        'cantidad' => $item['cantidad'],
                    ]),
                ];
            });
    }
    // App/Models/Pedido.php
    public function scopeDeMuestra($q)
    {
        return $q->where('tipo', 'MUESTRA');
    }
    public function scopeSoloPedidos($query)
    {
        return $query->where('pedido.tipo', 'PEDIDO'); // ✅ calificado
    }
    public function scopeEstatusMuestra($q, string $status)
    {
        return $q->where('estatus_muestra', $status);
    }
    public function ultimoEstado()
    {
        return $this->hasOne(\App\Models\PedidoEstado::class, 'pedido_id')
            ->latestOfMany('id'); // último por id
    }
    public function archivos()
    {
        return $this->hasMany(ArchivoPedido::class, 'pedido_id');
    }

    public function getClaveAttribute(): string{
         return "{$this->proyecto_id}-{$this->id}";
    }

    public function getTooltipClaveAttribute(): string
    {
        $desc = $this->descripcion_corta ?? '';
        return "Proyecto {$this->proyecto_id} - Pedido #{$this->id}: {$desc}";
    }

    /**
     * Todas las evidencias de entrega (tipo_carga = 3), más recientes primero.
     */
    public function archivosEvidencia()
    {
        return $this->hasMany(ArchivoPedido::class, 'pedido_id')
            ->where('tipo_carga', 3)
            ->orderByDesc('id');
    }

    /**
     * Solo la evidencia más reciente (atajo).
     * Compatible con Laravel 8+ usando ofMany.
     */
    public function evidenciaEntrega()
    {
        return $this->hasOne(ArchivoPedido::class, 'pedido_id')
            ->where('tipo_carga', 3)
            ->ofMany('id', 'max'); // <- sin closure como 3er arg
    }

    // App/Models/Pedido.php

    // URL al proyecto (route helper)
    public function getProyectoUrlAttribute(): string
    {
        return $this->proyecto_id
            ? route('proyecto.show', $this->proyecto_id)
            : '#';
    }

    // Anchor listo para usarse en la celda (con título y texto "proyecto-pedido")
    public function getClaveLinkAttribute(): string
    {
        if (!$this->proyecto_id) {
            return e("{$this->proyecto_id}-{$this->id}");
        }

        $href  = route('proyecto.show', $this->proyecto_id);
        $texto = "{$this->proyecto_id}-{$this->id}";

        return sprintf(
            '<a href="%s" class="text-blue-600 hover:underline">%s</a>',
            e($href),
            e($texto)
        );
    }

}
