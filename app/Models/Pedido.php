<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;
    protected $table = 'pedido'; // Nombre correcto de la tabla
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'id',
        'proyecto_id',
        'pre_proyecto_id',
        'producto_id',
        'cliente_id',
        'fecha_creacion',
        'total',
        'estatus',
    ];

    /**
     * Relación con la tabla de clientes.
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }


        /**
     * Relación con el modelo Proyecto.
     */
    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    /**
     * Relación con el modelo Producto.
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }



    // Relacion con el modelo de pedidoCaracteristica

    public function pedidoCaracteristicas()
    {
        return $this->hasMany(PedidoCaracteristica::class, 'pedido_id');
    }


    public function pedidoOpciones()
    {
        return $this->hasMany(PedidoOpcion::class, 'pedido_id');
    }


    public function pedidoTallas()
    {
        return $this->hasMany(PedidoTalla::class, 'pedido_id');
    }

    // /**
    //  * Relación con la tabla de proveedores.
    //  */
    // public function proveedor()
    // {
    //     return $this->belongsTo(Proveedor::class);
    // }

    // /**
    //  * Relación con la tabla de tallas.
    //  */
    // public function tallas()
    // {
    //     return $this->hasMany(Talla::class);
    // }
}
