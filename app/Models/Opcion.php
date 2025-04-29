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


    protected $fillable = ['nombre', 'pasos', 'minutoPaso', 'valoru', 'ind_activo'];

    public function caracteristicas()
    {
        return $this->belongsToMany(Caracteristica::class, 'caracteristica_opcion', 'opcion_id', 'caracteristica_id');
    }

    
}
