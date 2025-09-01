<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class HojaFiltroProduccion extends Model
{
    use HasFactory;

    protected $table = 'hojas_filtros_produccion';

    protected $fillable = [
        'nombre','slug','descripcion','role_id',
        'estados_permitidos','base_columnas','visible','orden',
    ];

    protected $casts = [
        'visible' => 'boolean',
        'estados_permitidos' => 'array',
        'base_columnas' => 'array',
    ];

    // Relación a filtros (pestañas)
    public function filtros()
    {
        return $this->belongsToMany(FiltroProduccion::class, 'hoja_filtro_produccion', 'hoja_id', 'filtro_produccion_id')
            ->withPivot(['orden'])
            ->withTimestamps()
            ->orderBy('hoja_filtro_produccion.orden');
    }

    public function rol()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'role_id');
    }

    // Scopes
    public function scopeVisibles($q){ return $q->where('visible', true); }

    public function scopeAccessibleBy($q, \App\Models\User $user)
    {
        return $q->where(function($qq) use ($user) {
            $qq->whereNull('role_id')
               ->orWhereIn('role_id', $user->roles()->pluck('id'));
        });
    }

    // Config columnas base con defaults
    public static function defaultBaseColumnas(): array
    {
        return [
            [ "key"=>"id",       "label"=>"ID",       "visible"=>true, "fixed"=>true,  "orden"=>1 ],
            [ "key"=>"proyecto", "label"=>"Proyecto", "visible"=>true, "fixed"=>false, "orden"=>2 ],
            [ "key"=>"producto", "label"=>"Producto", "visible"=>true, "fixed"=>false, "orden"=>3 ],
            [ "key"=>"total",    "label"=>"Total",    "visible"=>true, "fixed"=>false, "orden"=>4 ],
        ];
    }

    // Helper: columnas base normalizadas/ordenadas
    public function columnasBase(): \Illuminate\Support\Collection
    {
        $arr = $this->base_columnas ?: static::defaultBaseColumnas();
        return collect($arr)->sortBy('orden')->values();
    }
}
