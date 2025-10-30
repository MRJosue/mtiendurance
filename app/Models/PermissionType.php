<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionType extends Model
{
    protected $table = 'permission_types';

    protected $fillable = [
        'slug',
        'nombre',
        'orden',
    ];

    public function permissions()
    {
        // Usa el modelo Permission personalizado (ver abajo)
        return $this->hasMany(Permission::class, 'permission_type_id');
    }
}