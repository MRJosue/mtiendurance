<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoCaracteristica extends Model
{
    use HasFactory;

    protected $table = 'pedido_caracteristicas'; // Nombre de la tabla

    protected $fillable = [
        'pedido_id',
        'caracteristica_id',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function caracteristica()
    {
        return $this->belongsTo(Caracteristica::class);
    }
}
