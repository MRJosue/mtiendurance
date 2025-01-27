<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Caracteristica extends Model
{
    use HasFactory;

    protected $table = 'caracteristicas';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'string';

 
    protected $fillable = ['nombre', 'pasos', 'minutoPaso', 'valoru'];

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'producto_caracteristica');
    }

    public function opciones()
    {
        return $this->belongsToMany(Opcion::class, 'caracteristica_opcion')->withPivot('restriccion');
    }
}
