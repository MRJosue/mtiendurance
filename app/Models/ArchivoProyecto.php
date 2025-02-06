<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchivoProyecto extends Model
{
    use HasFactory;

    protected $table = 'archivos_proyecto';
    protected $fillable = [
        'proyecto_id',
        'pre_proyecto_id',
        'nombre_archivo',
        'ruta_archivo',
        'tipo_archivo',
        'usuario_id',
        'descripcion',

    ];


    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function preproyecto()
    {
        return $this->belongsTo(preproyecto::class, 'pre_proyecto_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
