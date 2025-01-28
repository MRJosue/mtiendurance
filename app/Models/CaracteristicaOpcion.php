<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaracteristicaOpcion extends Model
{
    use HasFactory;

    protected $table = 'caracteristica_opcion';

    protected $fillable = ['caracteristica_id', 'opcion_id'];
}
