<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FiltroProduccion extends Model
{
    use HasFactory;

    protected $table = 'filtros_produccion';

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'created_by',
        'visible',
        'orden',
        'config',
    ];

    protected $casts = [
        'visible' => 'boolean',
        'config'  => 'array',
    ];

    // Productos miembros (lista estática)
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'filtro_produccion_productos')
                    ->withTimestamps();
    }

    // Características como columnas (con metadatos de presentación)
public function caracteristicas()
{
    return $this->belongsToMany(
        \App\Models\Caracteristica::class,
        'filtro_produccion_caracteristicas',
        'filtro_produccion_id',
        'caracteristica_id'
    )->withPivot([
        'orden','label','visible','ancho',
        'render','multivalor_modo','max_items','fallback',
    ])->withTimestamps();
}

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes útiles
    public function scopeVisibles($query)
    {
        return $query->where('visible', true);
    }

    /**
     * IDs de productos del grupo (para filtrar pedidos: whereIn('producto_id', ...)).
     */
    public function productoIds(): \Illuminate\Support\Collection
    {
        return $this->productos()->pluck('productos.id');
    }

    /**
     * Definición de columnas (características) ya normalizada para la UI.
     */
public function columnas()
{
    return $this->caracteristicas()->get()->map(function ($car) {
        $pv = $car->pivot;

        // LEE el atributo real del pivote (evita colisión con la propiedad $visible de Eloquent)
        $visAttr = $pv->getAttribute('visible'); // o: $pv->getRawOriginal('visible');

        return [
            'id'              => $car->id,
            'nombre'          => $car->nombre,
            'orden'           => $pv->orden,
            'label'           => $pv->label ?: $car->nombre,
            'visible'         => is_null($visAttr) ? true : (bool) $visAttr,
            'ancho'           => $pv->ancho,
            'render'          => $pv->render ?? 'texto',
            'multivalor_modo' => $pv->multivalor_modo ?? 'inline',
            'max_items'       => (int) ($pv->max_items ?? 4),
            'fallback'        => $pv->fallback ?? '—',
        ];
    })->sortBy('orden')->values();
}
}