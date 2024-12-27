<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    // Definir la tabla si no sigue la convención de nombres (en caso de que se use un nombre distinto)
    // protected $table = 'roles';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array
     */
    protected $fillable = ['name', 'guard_name'];

    /**
     * Obtener los usuarios que tienen este rol.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
