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
        'nombre_archivo',
        'ruta_archivo',
        'tipo_archivo',
        'fecha_subida',
        'usuario_id',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
