<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Talla extends Model
{
    use HasFactory;

    /**
     * Indica si el modelo tiene una clave primaria auto-incremental.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * El tipo de clave primaria.
     *
     * @var string
     */
    protected $keyType = 'int';



    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // Relación con los grupos de tallas
    public function gruposTallas()
    {
        return $this->belongsToMany(GrupoTalla::class, 'grupo_tallas_detalle', 'talla_id', 'grupo_talla_id');
    }
        

}
