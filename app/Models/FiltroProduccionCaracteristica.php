<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiltroProduccionCaracteristica extends Model
{
    use HasFactory;

    protected $table = 'filtro_produccion_caracteristicas';

    protected $fillable = [
        'filtro_produccion_id',
        'caracteristica_id',
        'orden',
        'label',
        'visible',
        'ancho',
        'render',
        'multivalor_modo',
        'max_items',
        'fallback',
    ];

    protected $casts = [
        'visible'   => 'boolean',
        'max_items' => 'integer',
    ];

    public function filtro()
    {
        return $this->belongsTo(FiltroProduccion::class, 'filtro_produccion_id');
    }

    public function caracteristica()
    {
        return $this->belongsTo(Caracteristica::class, 'caracteristica_id');
    }
}