<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

use App\Models\DireccionEntrega;
use App\Models\DireccionFiscal;
use App\Models\Ciudad;
use App\Models\Pais;
use App\Models\Estado;
use App\Models\TipoEnvio;


use Illuminate\Support\Facades\Log;

class Proyecto extends Model
{
    use HasFactory;

    protected $table = 'proyectos';
    protected $primaryKey = 'id';
    public $incrementing = true; // El campo ID no es auto-incremental
    protected $keyType = 'int'; // Tipo del campo ID como string


    protected $fillable = [
        'usuario_id',
        'direccion_fiscal',
        'direccion_fiscal_id',
        'direccion_entrega',
        'direccion_entrega_id',
        'nombre',
        'descripcion',
        'id_tipo_envio',
        'tipo',
        'numero_muestras',
        'estado',
        'fecha_creacion',
        'fecha_produccion',
        'fecha_embarque',
        'fecha_entrega',
        'categoria_sel',
        'producto_sel',
        'caracteristicas_sel',
        'opciones_sel',
        'total_piezas_sel',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'fecha_produccion' => 'date',
        'fecha_embarque' => 'date',
        'fecha_entrega' => 'date',
        'categoria_sel' => 'array',
        'producto_sel' => 'array',
        'caracteristicas_sel' => 'array',
        'opciones_sel' => 'array',
        'total_piezas_sel' => 'array',
    ];


        // Lista de estados fijos en orden correcto
        protected static $estados = [
            'PENDIENTE', 'ASIGNADO', 'REVISION', 'DISEÑO APROBADO'
        ];


    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }


    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'proyecto_id');

    }

    public function archivos()
    {
        return $this->hasMany(ArchivoProyecto::class, 'proyecto_id');
    }

    public function chat()
    {
        return $this->hasOne(Chat::class, 'proyecto_id');
    }

    public function proyectoOrigen()
    {
        return $this->hasOne(Proyecto_Referencia::class, 'proyecto_id');
    }

    public function proyectosClonados()
    {
        return $this->hasMany(Proyecto_Referencia::class, 'proyecto_origen_id');
    }



    public function actualizarEstado($accion)
    {
        $estados = self::$estados; // Obtener lista de estados
        $indiceActual = array_search($this->estado, $estados);

        Log::emergency("Control estados", ['estados' => $estados]);
        Log::emergency("Control en el indiceActual", ['indiceActual' => $indiceActual]);


        // Si el estado actual no está en la lista, hay un error
        if ($indiceActual === false) {
            Log::error("Estado desconocido en el proyecto", ['proyecto_id' => $this->id, 'estado' => $this->estado]);
            return false;
        }

        // Manejo especial para "RECHAZADO"
        if ($accion === 'rechazar') {
            if ($this->estado === 'RECHAZADO') {
                Log::warning("El proyecto ya está en estado RECHAZADO", ['proyecto_id' => $this->id]);
                return false;
            }
            return $this->cambiarEstado('RECHAZADO');
        }

        // Determinar el nuevo estado
        if ($accion === 'siguiente') {

            if ($indiceActual < count($estados) - 1) { // Evita avanzar después de 'COMPLETADO'
                $nuevoEstado = $estados[$indiceActual + 1];
            } else {
                Log::warning("El proyecto ya está en el estado final", ['proyecto_id' => $this->id, 'estado' => $this->estado]);
                return false;
            }
        } elseif ($accion === 'anterior') {
            if ($indiceActual > 0) { // Evita retroceder antes de 'PENDIENTE'
                $nuevoEstado = $estados[$indiceActual - 1];
            } else {
                Log::warning("El proyecto ya está en el estado inicial", ['proyecto_id' => $this->id, 'estado' => $this->estado]);
                return false;
            }
        } else {
            Log::error("Acción inválida en actualizarEstado", ['proyecto_id' => $this->id, 'accion' => $accion]);
            return false;
        }

        return $this->cambiarEstado($nuevoEstado);
    }

    /**
     * Aplica el cambio de estado y lo guarda en `proyecto_estados`
     */
    private function cambiarEstado($nuevoEstado)
    {
        $estadoAnterior = $this->estado;

        // Actualizar estado del proyecto
        $this->update(['estado' => $nuevoEstado]);

        // Registrar en `proyecto_estados`
        proyecto_estados::create([
            'proyecto_id' => $this->id,
            'estado' => $nuevoEstado,
            'fecha_inicio' => now(),
            'usuario_id' => Auth::id(),
        ]);

        Log::info("Estado actualizado", [
            'proyecto_id' => $this->id,
            'anterior' => $estadoAnterior,
            'nuevo' => $nuevoEstado,
            'usuario' => Auth::id(),
        ]);

        return true;
    }
}
