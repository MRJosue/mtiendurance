<?php

namespace App\Services\Produccion;

use App\Models\HojaFiltroProduccion;
use App\Models\Pedido;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class HojaViewerQuery
{
    public function build(array $params): Builder
    {
        /** @var \App\Models\User|null $user */
        $user = $params['user'] ?? null;
        /** @var HojaFiltroProduccion $hoja */
        $hoja = $params['hoja'];
        /** @var Collection<int, int>|array<int, int> $productoIds */
        $productoIds = $params['producto_ids'] ?? collect();

        if (is_array($productoIds)) {
            $productoIds = collect($productoIds);
        }

        $search = trim((string) ($params['search'] ?? ''));
        $filters = $params['filters'] ?? [];
        $filtersCar = $params['filters_car'] ?? [];
        $sortColumn = $params['sort_column'] ?? null;
        $sortDirection = $params['sort_direction'] ?? 'asc';

        $query = Pedido::query()
            ->from('pedido')
            ->leftJoin('proyectos as pr', 'pr.id', '=', 'pedido.proyecto_id')
            ->leftJoin('productos as pd', 'pd.id', '=', 'pedido.producto_id')
            ->leftJoin('users as us', 'us.id', '=', 'pr.usuario_id')
            ->leftJoin('estados_pedido as ep', 'ep.id', '=', 'pedido.estado_id')
            ->select('pedido.*')
            ->with([
                'producto:id,nombre',
                'proyecto:id,nombre,estado',
                'estadoPedido:id,nombre,color',
                'usuario:id,name',
            ])
            ->soloPedidos();

        $this->applyRoleRestrictions($query, $user);
        $this->applyHojaFilters($query, $hoja, $productoIds);
        $this->applyGlobalSearch($query, $search);
        $this->applyBaseFilters($query, $filters);
        $this->applyCharacteristicsFilters($query, $filtersCar);
        $this->applySorting($query, $sortColumn, $sortDirection);

        return $query;
    }

    protected function applyRoleRestrictions(Builder $query, $user): void
    {
        $query->when($user, function ($qq) use ($user) {
            if ($user->hasRole('admin') || $user->can('tablaPedidos-ver-todos-los-pedidos')) {
                return;
            }

            if ($user->hasRole('cliente_principal')) {
                $idsUsuarios = collect($user->subordinados ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->filter(fn ($id) => $id > 0)
                    ->prepend((int) $user->id)
                    ->unique()
                    ->values()
                    ->all();

                $qq->whereIn('pr.usuario_id', $idsUsuarios);
                return;
            }

            if ($user->hasAnyRole(['cliente_subordinado', 'estaf'])) {
                $qq->where('pr.usuario_id', (int) $user->id);
                return;
            }

            $qq->where('pr.usuario_id', (int) $user->id);
        });
    }

    protected function applyHojaFilters(Builder $query, HojaFiltroProduccion $hoja, Collection $productoIds): void
    {
        $query
            ->when($productoIds->isNotEmpty(), fn ($qq) => $qq->whereIn('pedido.producto_id', $productoIds))
            ->when(!empty($hoja->estados_permitidos), fn ($qq) => $qq->whereIn('pedido.estado_id', $hoja->estados_permitidos))
            ->when(!empty($hoja->estados_diseno_permitidos), function ($qq) use ($hoja) {
                $permitidos = array_map(
                    fn ($s) => $s === 'RECHAZADO' ? 'DISEÑO RECHAZADO' : $s,
                    $hoja->estados_diseno_permitidos
                );

                $qq->whereIn('pr.estado', $permitidos);
            })
            ->when(!empty($hoja->estado_produccion_permitidos), function ($qq) use ($hoja) {
                $permitidos = array_values(array_filter($hoja->estado_produccion_permitidos));

                if (empty($permitidos)) {
                    return;
                }

                $permitidosSql = [];
                foreach ($permitidos as $permitido) {
                    $permitidosSql = array_merge($permitidosSql, self::estadoProduccionVariants($permitido));
                }

                $permitidosSql = array_values(array_unique(array_filter($permitidosSql)));

                $qq->whereIn('pedido.estado_produccion', $permitidosSql);
            })
            ->when(!empty($hoja->estado_proveedor_permitidos), function ($qq) use ($hoja) {
                $qq->whereIn('pedido.estatus_proveedor', array_values(array_filter($hoja->estado_proveedor_permitidos)));
            });
    }

    protected function applyGlobalSearch(Builder $query, string $term): void
    {
        if ($term === '') {
            return;
        }

        $prefix = $term . '%';

        $query->where(function ($searchQuery) use ($prefix, $term) {
            $searchQuery->where('pr.nombre', 'like', $prefix)
                ->orWhere('pd.nombre', 'like', $prefix)
                ->orWhere('us.name', 'like', $prefix)
                ->orWhere('ep.nombre', 'like', $prefix)
                ->orWhere('pedido.estatus_proveedor', 'like', $prefix);

            if (preg_match('/^\s*(\d+)\s*-\s*(\d+)\s*$/', $term, $matches)) {
                $proyectoId = (int) $matches[1];
                $pedidoId = (int) $matches[2];

                $searchQuery->orWhere(function ($idQuery) use ($proyectoId, $pedidoId) {
                    $idQuery->where('pedido.proyecto_id', $proyectoId)
                        ->where('pedido.id', $pedidoId);
                });
            }

            if (ctype_digit($term)) {
                $numeric = (int) $term;
                $searchQuery->orWhere('pedido.id', $numeric)
                    ->orWhere('pedido.proyecto_id', $numeric);
            }
        });
    }

    protected function applyBaseFilters(Builder $query, array $filters): void
    {
        $query
            ->when(($idRaw = trim((string) Arr::get($filters, 'id', ''))) !== '', function ($qq) use ($idRaw) {
                if (preg_match('/^\s*(\d+)\s*-\s*(\d+)\s*$/', $idRaw, $matches)) {
                    $qq->where('pedido.proyecto_id', (int) $matches[1])
                        ->where('pedido.id', (int) $matches[2]);
                    return;
                }

                if (ctype_digit($idRaw)) {
                    $numeric = (int) $idRaw;
                    $qq->where(function ($idQuery) use ($numeric) {
                        $idQuery->where('pedido.id', $numeric)
                            ->orWhere('pedido.proyecto_id', $numeric);
                    });
                }
            })
            ->when(($value = trim((string) Arr::get($filters, 'proyecto', ''))) !== '', fn ($qq) => $qq->where('pr.nombre', 'like', $value . '%'))
            ->when(($value = trim((string) Arr::get($filters, 'producto', ''))) !== '', fn ($qq) => $qq->where('pd.nombre', 'like', $value . '%'))
            ->when(($value = trim((string) Arr::get($filters, 'cliente', ''))) !== '', fn ($qq) => $qq->where('us.name', 'like', $value . '%'))
            ->when(Arr::get($filters, 'estado_id'), fn ($qq, $estadoId) => $qq->where('pedido.estado_id', (int) $estadoId))
            ->when(($value = trim((string) Arr::get($filters, 'estado_disenio', ''))) !== '', fn ($qq) => $qq->where('pr.estado', $value))
            ->when(($value = trim((string) Arr::get($filters, 'estado_produccion', ''))) !== '', function ($qq) use ($value) {
                $variants = self::estadoProduccionVariants($value);

                if (!empty($variants)) {
                    $qq->whereIn('pedido.estado_produccion', $variants);
                }
            })
            ->when(($value = trim((string) Arr::get($filters, 'estado_proveedor', ''))) !== '', fn ($qq) => $qq->where('pedido.estatus_proveedor', $value))
            ->when(($value = trim((string) Arr::get($filters, 'total', ''))) !== '', fn ($qq) => $qq->where('pedido.total', $value))
            ->when(($value = Arr::get($filters, 'fecha_produccion_from')), fn ($qq) => $qq->where('pedido.fecha_produccion', '>=', $value . ' 00:00:00'))
            ->when(($value = Arr::get($filters, 'fecha_produccion_to')), fn ($qq) => $qq->where('pedido.fecha_produccion', '<=', $value . ' 23:59:59'))
            ->when(($value = Arr::get($filters, 'fecha_embarque_from')), fn ($qq) => $qq->where('pedido.fecha_embarque', '>=', $value . ' 00:00:00'))
            ->when(($value = Arr::get($filters, 'fecha_embarque_to')), fn ($qq) => $qq->where('pedido.fecha_embarque', '<=', $value . ' 23:59:59'))
            ->when(($value = Arr::get($filters, 'fecha_entrega_from')), fn ($qq) => $qq->where('pedido.fecha_entrega', '>=', $value . ' 00:00:00'))
            ->when(($value = Arr::get($filters, 'fecha_entrega_to')), fn ($qq) => $qq->where('pedido.fecha_entrega', '<=', $value . ' 23:59:59'));
    }

    protected function applyCharacteristicsFilters(Builder $query, array $filtersCar): void
    {
        if (empty($filtersCar)) {
            return;
        }

        foreach ($filtersCar as $carId => $value) {
            $value = trim((string) $value);

            if ($value === '') {
                continue;
            }

            $like = '%' . $value . '%';

            $query->whereExists(function ($subQuery) use ($carId, $like) {
                $subQuery->from('pedido_opciones as po')
                    ->join('caracteristica_opcion as co', 'co.opcion_id', '=', 'po.opcion_id')
                    ->join('opciones as o', 'o.id', '=', 'po.opcion_id')
                    ->whereColumn('po.pedido_id', 'pedido.id')
                    ->where('co.caracteristica_id', (int) $carId)
                    ->where('o.nombre', 'like', $like);
            });
        }
    }

    protected function applySorting(Builder $query, ?string $sortColumn, string $sortDirection): void
    {
        $direction = $sortDirection === 'desc' ? 'desc' : 'asc';

        if (!$sortColumn) {
            $query->orderBy('pedido.id', 'desc');
            return;
        }

        switch ($sortColumn) {
            case 'id':
                $query->orderBy('pedido.id', $direction);
                break;
            case 'proyecto':
                $query->orderBy('pedido.proyecto_id', $direction)
                    ->orderBy('pedido.id', 'desc');
                break;
            case 'producto':
                $query->orderBy('pd.nombre', $direction)
                    ->orderBy('pedido.id', 'desc');
                break;
            case 'cliente':
                $query->orderBy('us.name', $direction)
                    ->orderBy('pedido.id', 'desc');
                break;
            case 'estado':
                $query->orderBy('ep.nombre', $direction)
                    ->orderBy('pedido.id', 'desc');
                break;
            case 'estado_disenio':
                $query->orderBy('pr.estado', $direction)
                    ->orderBy('pedido.id', 'desc');
                break;
            case 'estado_produccion':
                $query->orderBy('pedido.estado_produccion', $direction)
                    ->orderBy('pedido.id', 'desc');
                break;
            case 'estado_proveedor':
                $query->orderBy('pedido.estatus_proveedor', $direction)
                    ->orderBy('pedido.id', 'desc');
                break;
            case 'total':
                $query->orderBy('pedido.total', $direction);
                break;
            case 'fecha_produccion':
            case 'fecha_embarque':
            case 'fecha_entrega':
                $query->orderBy('pedido.' . $sortColumn, $direction);
                break;
            default:
                $query->orderBy('pedido.id', 'desc');
                break;
        }
    }

    public static function canonicalEstadoProduccion(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $map = [
            'IMPRESION' => 'IMPRESIÓN',
            'FACTURACION' => 'FACTURACIÓN',
        ];

        return $map[self::normalizeKey($value)] ?? $value;
    }

    public static function estadoProduccionVariants(?string $value): array
    {
        $canonical = self::canonicalEstadoProduccion($value);
        if (!$canonical) {
            return [];
        }

        return array_values(array_unique([
            $canonical,
            self::normalizeKey($canonical),
        ]));
    }

    protected static function normalizeKey(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $upper = mb_strtoupper($value, 'UTF-8');
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $upper);
        $ascii = $ascii !== false ? $ascii : $upper;
        $ascii = preg_replace('/\s+/', ' ', $ascii);

        return trim((string) $ascii);
    }
}
