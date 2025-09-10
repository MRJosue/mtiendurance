<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HojaFiltroProduccion extends Model
{
    use HasFactory;

    protected $table = 'hojas_filtros_produccion';

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'role_id',
        'estados_permitidos',
        'base_columnas',
        'visible',
        'orden',
    ];

protected $casts = [
    'estados_permitidos' => 'array',
    'base_columnas'      => 'array',
    'visible'            => 'boolean',
];

    /** Filtros (pestañas) asignados a la hoja */
    public function filtros()
    {
        return $this->belongsToMany(FiltroProduccion::class, 'hoja_filtro_produccion', 'hoja_id', 'filtro_produccion_id')
            ->withPivot(['orden'])
            ->withTimestamps()
            ->orderBy('hoja_filtro_produccion.orden');
    }

    /** Rol Spatie que puede ver la hoja (opcional) */
    public function rol()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'role_id');
    }

    /** Mostrar solo hojas visibles */
    public function scopeVisibles($q)
    {
        return $q->where('visible', true);
    }

    /** Mostrar hojas accesibles para un usuario según su(s) rol(es) */
    public function scopeAccessibleBy($q, \App\Models\User $user)
    {
        return $q->where(function ($qq) use ($user) {
            $qq->whereNull('role_id')
               ->orWhereIn('role_id', $user->roles()->pluck('id'));
        });
    }

    /** Config por defecto de columnas base */
    public static function defaultBaseColumnas(): array
    {
        // ID y checkbox son siempre mostrados (id se fuerza visible/fijo en la vista)
        return [
            ['key' => 'id',      'label' => 'ID',       'visible' => true,  'fixed' => true,  'orden' => 1],
            ['key' => 'proyecto','label' => 'Proyecto', 'visible' => true,  'fixed' => false, 'orden' => 2],
            ['key' => 'producto','label' => 'Producto', 'visible' => true,  'fixed' => false, 'orden' => 3],
            ['key' => 'total',   'label' => 'Total',    'visible' => true,  'fixed' => false, 'orden' => 4],

            // NUEVA: mapea a pedido.estado
            ['key' => 'estado',  'label' => 'Estado',   'visible' => true,  'fixed' => false, 'orden' => 5],
        ];
    }

    /** Columnas base normalizadas/ordenadas (Collection) */
    public function columnasBase(): \Illuminate\Support\Collection
    {
    $cols = collect($this->base_columnas ?: static::defaultBaseColumnas());

    // Si falta 'estado', lo agregamos con orden adecuado
    if (!$cols->contains(fn($c) => ($c['key'] ?? null) === 'estado')) {
        $cols->push([ 'key' => 'estado', 'label' => 'Estado', 'visible' => true, 'fixed' => false, 'orden' => 4 ]);
    }

    return $cols->sortBy('orden')->values();
    }
}
