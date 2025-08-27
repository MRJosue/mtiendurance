<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiltroProduccionProducto extends Model
{
    use HasFactory;

    protected $table = 'filtro_produccion_productos';

    protected $fillable = [
        'filtro_produccion_id',
        'producto_id',
    ];

    public function filtro()
    {
        return $this->belongsTo(FiltroProduccion::class, 'filtro_produccion_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}