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
        'categoria_id'
    ];


    public function categoria()
    {
        return $this->belongsTo(\App\Models\Categoria::class, 'categoria_id');
    }
}
