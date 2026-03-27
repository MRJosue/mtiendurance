<?php

namespace App\Services\Preproyectos;

use App\Models\Caracteristica;
use App\Models\Opcion;
use App\Models\Talla;
use Illuminate\Support\Collection;

class ProductConfigurationBuilder
{
    public function build(?int $productoId, bool $mostrarSelectorArmado, mixed $seleccionArmado): array
    {
        if (!$productoId) {
            return [
                'tallas' => collect(),
                'tallasSeleccionadas' => [],
                'caracteristicas_sel' => [],
                'opciones_sel' => [],
                'caracteristicaOpcionesDisponibles' => [],
            ];
        }

        $tallas = $this->getTallasForProduct($productoId);
        $tallasSeleccionadas = [];

        foreach ($tallas as $talla) {
            foreach ($talla->gruposTallas as $grupo) {
                $tallasSeleccionadas[$grupo->id][$talla->id] = 0;
            }
        }

        $caracteristicas = $this->baseCaracteristicasQuery($productoId, $mostrarSelectorArmado, $seleccionArmado)->get();
        $opcionesDisponibles = $this->getOpcionesPorCaracteristica($caracteristicas->pluck('id'));

        $caracteristicasSel = $caracteristicas->map(function ($caracteristica) use ($opcionesDisponibles) {
            $opcionesArray = $opcionesDisponibles[$caracteristica->id] ?? [];

            return [
                'id' => $caracteristica->id,
                'nombre' => $caracteristica->nombre,
                'flag_seleccion_multiple' => $caracteristica->flag_seleccion_multiple,
                'opciones' => count($opcionesArray) === 1 ? $opcionesArray : [],
            ];
        })->toArray();

        $opcionesSel = collect($caracteristicasSel)
            ->filter(fn ($caracteristica) => count($caracteristica['opciones'] ?? []) === 1)
            ->mapWithKeys(fn ($caracteristica) => [$caracteristica['id'] => $caracteristica['opciones'][0]])
            ->toArray();

        return [
            'tallas' => $tallas,
            'tallasSeleccionadas' => $tallasSeleccionadas,
            'caracteristicas_sel' => $caracteristicasSel,
            'opciones_sel' => $opcionesSel,
            'caracteristicaOpcionesDisponibles' => $opcionesDisponibles,
        ];
    }

    protected function getTallasForProduct(int $productoId): Collection
    {
        return Talla::with('gruposTallas')
            ->whereHas('gruposTallas.productos', function ($query) use ($productoId) {
                $query->where('producto_id', $productoId);
            })
            ->get();
    }

    protected function baseCaracteristicasQuery(int $productoId, bool $mostrarSelectorArmado, mixed $seleccionArmado)
    {
        $query = Caracteristica::where('ind_activo', 1)
            ->whereHas('productos', function ($builder) use ($productoId) {
                $builder->where('producto_id', $productoId);
            });

        if ($mostrarSelectorArmado && $seleccionArmado !== null) {
            $query->whereHas('productos', function ($builder) use ($productoId, $seleccionArmado) {
                $builder->where('producto_id', $productoId)
                    ->where('producto_caracteristica.flag_armado', $seleccionArmado);
            });
        }

        return $query;
    }

    protected function getOpcionesPorCaracteristica(Collection $caracteristicaIds): array
    {
        if ($caracteristicaIds->isEmpty()) {
            return [];
        }

        return Opcion::query()
            ->where('ind_activo', 1)
            ->whereHas('caracteristicas', function ($query) use ($caracteristicaIds) {
                $query->whereIn('caracteristicas.id', $caracteristicaIds);
            })
            ->with(['caracteristicas' => function ($query) use ($caracteristicaIds) {
                $query->whereIn('caracteristicas.id', $caracteristicaIds);
            }])
            ->get()
            ->reduce(function ($carry, $opcion) {
                foreach ($opcion->caracteristicas->pluck('id') as $caracteristicaId) {
                    $carry[$caracteristicaId][] = [
                        'id' => $opcion->id,
                        'nombre' => $opcion->nombre,
                        'valoru' => $opcion->valoru,
                    ];
                }

                return $carry;
            }, []);
    }
}
