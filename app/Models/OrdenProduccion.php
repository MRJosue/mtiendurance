<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class OrdenProduccion extends Model
{
    use HasFactory;

    protected $table = 'ordenes_produccion';

    protected $fillable = [
        'nombre',
        'tipo',
        'estado',
        'usuario_id',
        'crete_user',
        'fecha_inicio',
        'fecha_fin',
    ];

    public function responsable()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'crete_user');
    }

    public function tareas()
    {
        return $this->hasMany(TareaProduccion::class, 'orden_id');
    }
}