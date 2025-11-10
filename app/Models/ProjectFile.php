<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectFile extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla.
     *
     * Si mantienes la convención plural, Laravel la detectará automáticamente.
     * Si la tabla sigue llamándose "project_file", descomenta la siguiente línea.
     */
    protected $table = 'project_file';
    // protected $table = 'project_file';

    /**
     * Clave primaria personalizada.
     */
    protected $primaryKey = 'project_file_id';

    /**
     * Campos asignables en masa.
     */
    protected $fillable = [
        'project_id',
        'description',
        'name',
        'visibility_client',
        'visibility_staff',
        'size',
        'type',
        'type_id',
        'timestamp_upload',
    ];

    /**
     * Relación con el proyecto.
     */
    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'project_id');
    }

    /**
     * Accesor opcional: devuelve si el archivo es visible para el cliente.
     */
    public function getEsVisibleClienteAttribute(): bool
    {
        return $this->visibility_client === 1;
    }

    /**
     * Accesor opcional: devuelve si el archivo es visible para el staff.
     */
    public function getEsVisibleStaffAttribute(): bool
    {
        return $this->visibility_staff === 1;
    }
}
