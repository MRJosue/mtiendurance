<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    public    $incrementing = true;
    protected $keyType = 'string';

    protected $fillable = [

        'categoria_id',
        'nombre',
        'dias_produccion',
        'flag_armado',
        'ind_activo'
    ];


    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
    
    

    public function caracteristicas()
    {
        return $this->belongsToMany(Caracteristica::class, 'producto_caracteristica', 'producto_id', 'caracteristica_id');
    }

    public function caracteristicasNoArmado()
    {
        return $this->belongsToMany(Caracteristica::class, 'producto_caracteristica')
            ->withPivot('flag_armado')
            ->wherePivot('flag_armado', 0);
    }


    public function caracteristicasArmado()
    {
        return $this->belongsToMany(Caracteristica::class, 'producto_caracteristica')
            ->withPivot('flag_armado')
            ->wherePivot('flag_armado', 1);
    }


    public function gruposTallas()
    {
        return $this->belongsToMany(GrupoTalla::class, 'producto_grupo_talla', 'producto_id', 'grupo_talla_id');
    }


}
