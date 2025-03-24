<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class proyecto_estados extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar de manera masiva.
     *
     * @var array
     */
    protected $fillable = [
        'proyecto_id',
        'estado',
        'fecha_inicio',
        'fecha_fin',
        'usuario_id',
        'comentario',
        'url',
        'last_uploaded_file_id',
    ];

    /**
     * Relación con el modelo Proyecto.
     *
     * Un estado pertenece a un proyecto.
     */
    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    /**
     * Relación con el modelo User.
     *
     * Un estado es asignado por un usuario.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
