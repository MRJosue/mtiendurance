<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoTalla extends Model
{
    use HasFactory;

    protected $table = 'pedido_tallas'; // Nombre de la tabla

    protected $fillable = [
        'pedido_id',
        'talla_id',
        'cantidad',
    ];

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function talla()
    {
        return $this->belongsTo(Talla::class);
    }
}
