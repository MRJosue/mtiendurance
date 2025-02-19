<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    
    use HasFactory;
    protected $fillable = [
        'usuario_id',
        'nombre_empresa',
        'contacto_principal',
        'telefono',
        'email',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Relación con la tabla de pedidos.
     */
    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }
}
