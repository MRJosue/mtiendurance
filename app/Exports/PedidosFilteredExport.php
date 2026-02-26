<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PedidosFilteredExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected Builder $query
    ) {}

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'ID Pedido',
            'Tipo',
            'ID Proyecto',
            'Proyecto',
            'Cliente',
            'Producto',
            'Categoría',
            'Total',
            'Estado Diseño',
            'Estado Pedido',
            'Producción',
            'Entrega',
            'Activo',
            'Creado',
        ];
    }

    public function map($pedido): array
    {
        return [
            $pedido->id,
            $pedido->tipo,
            $pedido->proyecto_id,
            $pedido->proyecto?->nombre ?? '—',
            $pedido->usuario?->name ?? $pedido->proyecto?->user?->name ?? '—',
            $pedido->producto?->nombre ?? '—',
            $pedido->producto?->categoria?->nombre ?? '—',
            $pedido->total ?? 0,
            $pedido->proyecto?->estado ?? '—',
            $pedido->estado ?? '—',
            optional($pedido->fecha_produccion)->format('Y-m-d'),
            optional($pedido->fecha_entrega)->format('Y-m-d'),
            ($pedido->ind_activo ?? 1) ? 'Sí' : 'No',
            optional($pedido->created_at)->format('Y-m-d H:i'),
        ];
    }
}