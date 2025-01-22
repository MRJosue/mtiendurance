<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CiudadesTipoEnvio extends Model
{
    protected $table = 'ciudades_tipo_envio';
    protected $fillable = ['ciudad_id', 'tipo_envio_id'];
}
