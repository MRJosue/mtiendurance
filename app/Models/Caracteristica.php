<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Caracteristica extends Model
{
    use HasFactory;

    protected $table = 'caracteristicas';

    protected $fillable = ['nombre', 'flag_seleccion_multiple','ind_activo'];

    protected $casts = [
        'flag_seleccion_multiple' => 'boolean', // Asegurar que se maneja como booleano
    ];


    public function categorias()
    {
        return $this->belongsToMany(Categoria::class, 'categoria_caracteristica', 'caracteristica_id', 'categoria_id');
    }
    

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'producto_caracteristica', 'caracteristica_id', 'producto_id');
    }

    public function opciones()
    {
        return $this->belongsToMany(Opcion::class, 'caracteristica_opcion', 'caracteristica_id', 'opcion_id')->orderBy('nombre', 'asc');
    }
}
