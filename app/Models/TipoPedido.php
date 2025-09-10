<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoPedido extends Model
{
    use HasFactory;

    protected $table = 'tipos_pedido';

    protected $fillable = [
        'nombre',
        'slug',
        'orden',
        'ind_activo',
    ];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'tipo_id');
    }
}