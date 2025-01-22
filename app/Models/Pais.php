<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pais extends Model
{
    use HasFactory;

    protected $table = 'paises'; // Nombre de la tabla

    protected $fillable = ['nombre'];

    public function estados()
    {
        return $this->hasMany(Estado::class);
    }
}
