<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class GrupoOrden extends Model
{
    protected $table = 'grupos_orden';
    protected $fillable = ['nombre'];
    

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'grupo_orden_permission')
                    ->withPivot('orden')
                    ->withTimestamps()
                    ->orderBy('grupo_orden_permission.orden');
    }
}