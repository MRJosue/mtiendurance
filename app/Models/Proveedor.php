<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;
    protected $table = 'proveedores';

    protected $fillable = [
        'usuario_id',
        'nombre_empresa',
        'contacto_principal',
    ];

    /**
     * Relación con la tabla de pedidos.
     */
    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
