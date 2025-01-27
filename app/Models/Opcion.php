<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Opcion extends Model
{
    use HasFactory;

    protected $table = 'opciones';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'string';


    protected $fillable = ['nombre', 'pasos', 'minutoPaso', 'valoru'];

    public function caracteristicas()
    {
        return $this->belongsToMany(Caracteristica::class, 'caracteristica_opcion')->withPivot('restriccion');
    }

}
