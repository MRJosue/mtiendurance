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

        'nombre',
        'dias_produccion',
        'flag_armado'
    ];


    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
    
    

    public function caracteristicas()
    {
        return $this->belongsToMany(Caracteristica::class, 'producto_caracteristica', 'producto_id', 'caracteristica_id');
    }

}
