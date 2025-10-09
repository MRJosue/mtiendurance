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
        'estados_diseno_permitidos',
        'base_columnas',
        'menu_config',
        'visible',
        'orden',
    ];

    protected $casts = [
        'estados_permitidos' => 'array',
        'estados_diseno_permitidos' => 'array',
        'base_columnas'      => 'array',
        'menu_config'      => 'array',
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
    // Siempre presentes
        $base = [
            ['key' => 'id',       'label' => 'ID',        'visible' => true,  'fixed' => true,  'orden' => 1],
            ['key' => 'proyecto', 'label' => 'Proyecto',  'visible' => true,  'fixed' => false, 'orden' => 2],
            ['key' => 'cliente',  'label' => 'Cliente',   'visible' => true,  'fixed' => false, 'orden' => 3],
            ['key' => 'producto', 'label' => 'Producto',  'visible' => true,  'fixed' => false, 'orden' => 4],
            ['key' => 'total',    'label' => 'Total',     'visible' => true,  'fixed' => false, 'orden' => 5],
            ['key' => 'estado',   'label' => 'Estado',    'visible' => true,  'fixed' => false, 'orden' => 6],
            ['key' => 'estado_disenio', 'label' => 'Estado Diseño', 'visible' => true, 'fixed' => false, 'orden' => 7],
        ];


    // Añade fechas si no existen
        $maxOrden = (int) collect($base)->max('orden');

        $add = function (&$arr, string $key, string $label) use (&$maxOrden) {
            if (!collect($arr)->contains(fn($c) => ($c['key'] ?? null) === $key)) {
                $arr[] = [
                    'key'     => $key,
                    'label'   => $label,
                    'visible' => true,
                    'fixed'   => false,
                    'orden'   => ++$maxOrden,
                ];
            }
        };

        $add($base, 'fecha_produccion', 'F. Producción');
        $add($base, 'fecha_embarque',   'F. Embarque');
        $add($base, 'fecha_entrega',    'F. Entrega');

        return $base;
}

    /** Columnas base normalizadas/ordenadas (Collection) */
    public function columnasBase(): \Illuminate\Support\Collection
    {
        $cols = collect($this->base_columnas ?: static::defaultBaseColumnas());

        // Asegurar estado y fechas (por compatibilidad hacia atrás)
        $ensure = function (string $key, string $label) use (&$cols) {
            if (!$cols->contains(fn($c) => ($c['key'] ?? null) === $key)) {
                $cols->push([
                    'key'     => $key,
                    'label'   => $label,
                    'visible' => true,
                    'fixed'   => false,
                    'orden'   => (int) ($cols->max('orden') ?? 0) + 1,
                ]);
            }
        };

        $ensure('estado',           'Estado');
        $ensure('estado_disenio',   'Estado Diseño'); 
        $ensure('fecha_produccion', 'F. Producción');
        $ensure('fecha_embarque',   'F. Embarque');
        $ensure('fecha_entrega',    'F. Entrega');

        return $cols->sortBy('orden')->values();
    }
}
