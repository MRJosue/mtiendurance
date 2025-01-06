<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'email',
        'telefono',
    ];

    /**
     * Relación con la tabla de pedidos.
     */
    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }
}
