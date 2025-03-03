<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoGrupoTalla extends Model
{
    use HasFactory;

    protected $table = 'producto_grupo_talla';
    protected $fillable = ['producto_id', 'grupo_talla_id'];

    // Relación con Producto
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    // Relación con GrupoTalla
    public function grupoTalla()
    {
        return $this->belongsTo(GrupoTalla::class);
    }
}
