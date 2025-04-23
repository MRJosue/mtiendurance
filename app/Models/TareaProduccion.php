<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TareaProduccion extends Model
{
    use HasFactory;

    protected $table = 'tareas_produccion';

    protected $fillable = [
        
        'usuario_id',
        'crete_user',
        'tipo',
        'descripcion',
        'estado',
        'disenio_flag_first_proceso',
        'fecha_inicio',
        'fecha_fin',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }


    public function responsable()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'crete_user');
    }

    public function pedidos()
    {
        return $this->belongsToMany(Pedido::class, 'pedido_tarea', 'tarea_produccion_id', 'pedido_id');
    }
}