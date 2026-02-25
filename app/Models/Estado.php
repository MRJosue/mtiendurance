<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    protected $fillable = ['nombre', 'pais_id'];

    public function pais()
    {
        return $this->belongsTo(Pais::class);
    }

    public function tipoEnvios()
    {
        return $this->belongsToMany(
            \App\Models\TipoEnvio::class,
            'estado_tipo_envio',   // ✅ tu tabla pivote real
            'estado_id',
            'tipo_envio_id'
        );
    }


    public function syncTiposEnvio(array $tipoEnvioIds)
    {
        $this->tipoEnvios()->sync($tipoEnvioIds);
    }

    
}