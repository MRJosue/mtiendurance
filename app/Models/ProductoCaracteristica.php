<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoCaracteristica extends Model
{
    use HasFactory;

    protected $table = 'producto_caracteristica';

    protected $fillable = ['producto_id', 'caracteristica_id'];
}
