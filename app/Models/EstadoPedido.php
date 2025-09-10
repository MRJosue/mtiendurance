<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EstadoPedido extends Model
{
    use HasFactory;

    protected $table = 'estados_pedido';

    protected $fillable = [
        'nombre', 'slug', 'orden', 'color', 'ind_activo',
    ];

    public function pedidos()
    {
        // tu tabla es singular "pedido"
        return $this->hasMany(Pedido::class, 'estado_id');
    }

    public static function idPorNombre(string $nombre): ?int
    {
        return static::where('nombre', $nombre)->value('id');
    }

    public static function idPorSlug(string $slug): ?int
    {
        return static::where('slug', $slug)->value('id');
    }
}