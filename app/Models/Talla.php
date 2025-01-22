<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Talla extends Model
{
    use HasFactory;




    /**
     * Indica si el modelo tiene una clave primaria auto-incremental.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * El tipo de clave primaria.
     *
     * @var string
     */
    protected $keyType = 'int';



    protected $fillable = [
        'pedido_id',
        'nombre',
        'cantidad',
    ];

    /**
     * Relación con la tabla de pedidos.
     */
    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}
