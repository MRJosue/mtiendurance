<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreProyecto extends Model
{
    protected $table = 'pre_proyectos';

    protected $fillable = [
        'usuario_id',
        'nombre',
        'descripcion',
        'estado',
        'fecha_entrega',
    ];
}
