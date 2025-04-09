<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoEstado extends Model
{
    protected $table = 'pedido_estados';

    protected $fillable = [
        'pedido_id',
        'proyecto_id',
        'usuario_id',
        'estado',
        'fecha_inicio',
        'fecha_fin',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}