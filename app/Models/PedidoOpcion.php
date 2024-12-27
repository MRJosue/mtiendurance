<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PedidoOpcion extends Model
{
    use HasFactory;

    protected $table = 'pedido_opciones'; // Nombre de la tabla

    use HasFactory;

    protected $fillable = [
        'pedido_id',
        'opcion_id',
        'valor',
    ];

    public $incrementing = true; // La clave primaria no es auto-incremental
    protected $keyType = 'int'; // Clave primaria es de tipo string (UUID)

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function opcion()
    {
        return $this->belongsTo(Opcion::class);
    }

}
