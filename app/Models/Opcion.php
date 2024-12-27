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

    protected $fillable = ['id', 'valor', 'caracteristica_id'];



    public function caracteristica()
    {
        return $this->belongsTo(\App\Models\Caracteristica::class, 'caracteristica_id');
    }

}
