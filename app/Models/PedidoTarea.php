<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoTarea extends Model
{
    use HasFactory;

    protected $table = 'pedido_tarea';

    protected $fillable = [
        'pedido_id',
        'tarea_produccion_id',
    ];
}
