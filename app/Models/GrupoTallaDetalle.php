<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupoTallaDetalle extends Model
{
    use HasFactory;

    protected $table = 'grupo_tallas_detalle';
    protected $fillable = ['grupo_talla_id', 'talla_id'];

    // Relación con GrupoTalla
    public function grupoTalla()
    {
        return $this->belongsTo(GrupoTalla::class);
    }

    // Relación con Talla
    public function talla()
    {
        return $this->belongsTo(Talla::class);
    }
}
