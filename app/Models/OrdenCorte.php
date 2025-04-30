<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenCorte extends Model
{
    use HasFactory;

    protected $table = 'orden_corte';

    protected $fillable = [
        'orden_produccion_id',
        'tallas',
        'tallas_entregadas',
        'total',
        'caracteristicas',
        'fecha_inicio',
    ];

    protected $casts = [
        'tallas' => 'array',
        'tallas_entregadas' => 'array',
        'caracteristicas' => 'array',
        'fecha_inicio' => 'date',
    ];

    public function orden()
    {
        return $this->belongsTo(OrdenProduccion::class, 'orden_produccion_id');
    }
}