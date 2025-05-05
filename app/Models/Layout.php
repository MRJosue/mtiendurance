<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Layout extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'producto_id',
        'categoria_id',
        'usuario_id',
        'ind_activo',
    ];

    public function elementos()
    {
        return $this->hasMany(LayoutElemento::class);
    }

    

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}