<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class OrdenProduccion extends Model
{
    use HasFactory;

    protected $table = 'ordenes_produccion';

    protected $fillable = [
        'create_user',           // corregido typo
        'assigned_user_id',      // nuevo campo para usuario asignado
        'tipo',
        'estado',
        'flujo_id',
        'fecha_sin_iniciar',
        'fecha_en_proceso',
        'fecha_terminado',
        'fecha_cancelado',
    ];

    // Relación con orden de corte
    public function ordenCorte()
    {
        return $this->hasOne(OrdenCorte::class, 'orden_produccion_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'create_user');
    }

    // Relación con usuario asignado
    public function usuarioAsignado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /**
     * Flujo de producción que define los pasos
     */
    public function flujo(): BelongsTo
    {
        return $this->belongsTo(FlujoProduccion::class, 'flujo_id');
    }

    /**
     * Instancias de cada paso inyectado al crear la orden
     */
    public function ordenPasos(): HasMany
    {
        return $this->hasMany(OrdenPaso::class, 'orden_produccion_id');
    }

    /**
     * Relación many-to-many con pedidos
     */
    public function pedidos(): BelongsToMany
    {
        return $this->belongsToMany(Pedido::class, 'pedido_orden_produccion');
    }


    public function getUltimoEstatusOrdenProduccionAttribute()
    {
        $orden = $this->ordenesProduccion()->orderByDesc('created_at')->first();
        if ($orden) {
            return ($orden->tipo ?? 'N/D') . ':' . ($orden->estado ?? 'N/D');
        }
        return null;
    }

    // Relación con órdenes de producción (muchos a muchos)
    public function ordenesProduccion()
    {
        return $this->belongsToMany(OrdenProduccion::class, 'pedido_orden_produccion');
    }
}