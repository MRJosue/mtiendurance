<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarea extends Model
{
    use HasFactory;

    protected $table = 'tareas';

    protected $fillable = [
        'proyecto_id',
        'staff_id',
        'descripcion',
        'estado',
    ];


    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }


    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
