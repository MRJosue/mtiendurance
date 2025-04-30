<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class OrdenProduccion extends Model
{
    use HasFactory;

    protected $table = 'ordenes_produccion';

    protected $fillable = [
        'crete_user',
        'tipo',
    ];

    // Relación con pedidos (muchos a muchos)
    public function pedidos()
    {
        return $this->belongsToMany(Pedido::class, 'pedido_orden_produccion');
    }

    // Relación con orden de corte
    public function ordenCorte()
    {
        return $this->hasOne(OrdenCorte::class, 'orden_produccion_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'crete_user');
    }
}