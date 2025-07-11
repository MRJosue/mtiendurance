<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class OrdenPaso extends Model
{
    use HasFactory;

    protected $table = 'orden_paso';

    protected $fillable = [
        'orden_produccion_id',
        'nombre',
        'grupo_paralelo',
        'estado',
        'fecha_inicio',
        'fecha_fin',
    ];

    /**
     * Orden de producción propietaria
     */
    public function ordenProduccion(): BelongsTo
    {
        return $this->belongsTo(OrdenProduccion::class, 'orden_produccion_id');
    }

    /**
     * Tareas de producción ligadas a este paso
     */
    public function tareasProduccion(): HasMany
    {
        return $this->hasMany(TareaProduccion::class, 'orden_paso_id');
    }
}