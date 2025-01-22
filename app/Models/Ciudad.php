<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ciudad extends Model
{
    protected $fillable = ['nombre', 'estado_id'];

    protected $table = 'ciudades';

    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }

    public function tipoEnvios()
    {
        return $this->belongsToMany(TipoEnvio::class, 'ciudades_tipo_envio');
    }

    /**
     * Sincroniza los tipos de envío con la ciudad.
     *
     * @param array $tipoEnvioIds
     */
    public function syncTiposEnvio(array $tipoEnvioIds)
    {
        $this->tipoEnvios()->sync($tipoEnvioIds);
    }
}