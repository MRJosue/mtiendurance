<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoEnvio extends Model
{
    protected $fillable = ['nombre', 'descripcion', 'dias_envio'];
    protected $table = 'tipo_envio';
    public function ciudades()
    {
        return $this->belongsToMany(Ciudad::class, 'ciudades_tipo_envio');
    }
}
