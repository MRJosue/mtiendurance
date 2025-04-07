<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupoTalla extends Model
{
    use HasFactory;

    protected $table = 'grupos_tallas';
    protected $fillable = ['nombre', 'ind_activo'];

    // Relación con las tallas a través de GrupoTallaDetalle
    public function tallas()
    {
        return $this->belongsToMany(Talla::class, 'grupo_tallas_detalle', 'grupo_talla_id', 'talla_id');
    }

    // Relación con los productos a través de ProductoGrupoTalla
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'producto_grupo_talla', 'grupo_talla_id', 'producto_id');
    }
}
