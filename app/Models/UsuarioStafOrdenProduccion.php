<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioStafOrdenProduccion extends Model
{
       use HasFactory;

    protected $table = 'usuario_staf_ordenes_produccion';

    protected $fillable = [
        'orden_produccion_id',
        'create_user',
        'assigned_user_id',
        'cantidad_entregada',
        'cantidad_desperdicio',
        'total_entregado',
        'flag_activo',
    ];

    // Relación con OrdenProduccion
    public function ordenProduccion()
    {
        return $this->belongsTo(OrdenProduccion::class, 'orden_produccion_id');
    }

    // Usuario que creó el registro
    public function creador()
    {
        return $this->belongsTo(User::class, 'create_user');
    }

    // Usuario asignado (puede ser null)
    public function usuarioAsignado()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    
}
