<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proyecto_Referencia extends Model
{
    use HasFactory;

    protected $fillable = [
        'proyecto_id',
        'proyecto_origen_id',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function proyectoOrigen()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_origen_id');
    }
}
