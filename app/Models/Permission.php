<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    // Añadimos los campos nuevos para mass assignment
    protected $fillable = [
        'name',
        'guard_name',
        'nombre',             // NUEVO: nombre visible para el usuario
        'orden',              // NUEVO: orden opcional
        'permission_type_id', // NUEVO: FK opcional a permission_types
    ];

    protected $casts = [
        'orden' => 'integer',
        'permission_type_id' => 'integer',
    ];

    public function type()
    {
        return $this->belongsTo(PermissionType::class, 'permission_type_id');
    }

    // Helper para mostrar 'nombre' y, si no existe, caer a 'name'
    public function getNombreMostrarAttribute(): string
    {
        return $this->nombre ?: $this->name;
    }

    public function groups()
    {
        return $this->belongsToMany(GrupoOrden::class, 'grupo_orden_permission')
            ->withPivot('orden')
            ->withTimestamps();
    }
}