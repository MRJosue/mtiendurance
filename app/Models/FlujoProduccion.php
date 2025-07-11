<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlujoProduccion extends Model
{
    // Nombre de la tabla
    protected $table = 'flujos_produccion';

    // Campos asignables
    protected $fillable = [
        'nombre',
        'descripcion',
        'config',
    ];

    // Cast de config a array
    protected $casts = [
        'config' => 'array',
    ];

    /**
     * Las órdenes de producción que usan este flujo
     */
    public function ordenes(): HasMany
    {
        return $this->hasMany(OrdenProduccion::class, 'flujo_id');
    }
}