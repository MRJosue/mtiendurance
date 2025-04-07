<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Models\ArchivoProyecto;
use Illuminate\Support\Facades\Storage;


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
        'estatus',
        'direccion_fiscal_id',
        'direccion_fiscal',
        'direccion_entrega_id',
        'direccion_entrega',
        'tipo',
        'estado',
        'estado_produccion',
        'fecha_produccion',
        'fecha_embarque',
        'fecha_entrega',
        'id_tipo_envio',
        'url',
        'last_uploaded_file_id'
    ];

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


    public function pedidoTallas()
    {
        return $this->hasMany(PedidoTalla::class, 'pedido_id');
    }

    // Relación con TipoEnvio
    public function tipoEnvio()
    {
        return $this->belongsTo(TipoEnvio::class, 'id_tipo_envio');
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
    
    public static function crearMuestra($proyectoId, $data)
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
    
        // 🔍 Obtener el último archivo relacionado con el proyecto
        $ultimoArchivo = ArchivoProyecto::where('proyecto_id', $proyectoId)
            ->latest('created_at')
            ->first();
    
        $url = $ultimoArchivo ? Storage::disk('public')->url($ultimoArchivo->ruta_archivo) : null;
        $lastFileId = $ultimoArchivo?->id;
    
        // Crear pedido
        $pedido = self::create([
            'proyecto_id' => $proyecto->id,
            'producto_id' => $producto['id'],
            'user_id' => $proyecto->usuario_id,
            'cliente_id' => $clienteId,
            'fecha_creacion' => now(),
            'total' => $data['total'] ?? 0,
            'estatus' => $data['estatus'] ?? 'PENDIENTE',
            'direccion_fiscal_id' => $data['direccion_fiscal_id'] ?? null,
            'direccion_fiscal' => $data['direccion_fiscal'] ?? null,
            'direccion_entrega_id' => $data['direccion_entrega_id'] ?? null,
            'direccion_entrega' => $data['direccion_entrega'] ?? null,
            'tipo' => $data['tipo'] ?? 'MUESTRA',
            'estado' => $data['estado'] ?? 'POR PROGRAMAR',
            'fecha_produccion' => $data['fecha_produccion'] ?? null,
            'fecha_embarque' => $data['fecha_embarque'] ?? null,
            'fecha_entrega' => $data['fecha_entrega'] ?? null,
            'id_tipo_envio' =>  null,
            'url' => $url,
            'last_uploaded_file_id' => $lastFileId,
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

}
