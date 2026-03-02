<?php

namespace App\Exports;

use App\Models\Pedido;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class HojaPedidosSelectedExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected array $ids,
        protected array $baseCols = [],
        protected array $columnasFiltro = [],
    ) {}

    protected Collection $pedidos;
    protected array $valoresPorPedidoYCar = [];

    public function collection()
    {
        $this->pedidos = Pedido::query()
            ->from('pedido')
            ->whereIn('pedido.id', $this->ids)
            ->with([
                'producto:id,nombre',
                'proyecto:id,nombre,estado',
                'estadoPedido:id,nombre,color',
                'usuario:id,name',
            ])
            ->get();

        // Precarga de valores dinámicos (opciones por caracteristica) para columnasFiltro
        $carIds = collect($this->columnasFiltro)->pluck('id')->filter()->map(fn($v)=>(int)$v)->values()->all();

        if (!empty($carIds) && $this->pedidos->isNotEmpty()) {
            $rows = \DB::table('pedido_opciones as po')
                ->join('caracteristica_opcion as co', 'co.opcion_id', '=', 'po.opcion_id')
                ->join('opciones as o', 'o.id', '=', 'po.opcion_id')
                ->whereIn('po.pedido_id', $this->pedidos->pluck('id')->all())
                ->whereIn('co.caracteristica_id', $carIds)
                ->get(['po.pedido_id', 'co.caracteristica_id', 'o.nombre']);

            foreach ($rows as $r) {
                $this->valoresPorPedidoYCar[$r->pedido_id][$r->caracteristica_id][] = $r->nombre;
            }
        }

        return $this->pedidos;
    }

    public function headings(): array
    {
        $heads = ['ID'];

        // Base cols visibles
        foreach ($this->baseCols as $c) {
            $heads[] = $c['label'] ?? ucfirst((string)($c['key'] ?? ''));
        }

        // Dinámicas
        foreach ($this->columnasFiltro as $col) {
            $heads[] = $col['label'] ?? $col['nombre'] ?? ('CAR_'.$col['id']);
        }

        return $heads;
    }

    public function map($pedido): array
    {
        $row = [];
        $row[] = $pedido->id;

        // Base cols
        foreach ($this->baseCols as $c) {
            $key = (string)($c['key'] ?? '');

            $row[] = match ($key) {
                'proyecto'          => $pedido->proyecto->nombre ?? '—',
                'producto'          => $pedido->producto->nombre ?? '—',
                'cliente'           => $pedido->usuario->name ?? '—',
                'estado'            => $pedido->estadoPedido->nombre ?? ($pedido->estado ?? '—'),
                'estado_disenio'    => $pedido->proyecto->estado ?? '—',
                'estado_produccion' => $pedido->estado_produccion ?? '—',
                'total'             => (float)($pedido->total ?? 0),

                'fecha_produccion'  => optional($pedido->fecha_produccion)->format('Y-m-d') ?? '',
                'fecha_embarque'    => optional($pedido->fecha_embarque)->format('Y-m-d') ?? '',
                'fecha_entrega'     => optional($pedido->fecha_entrega)->format('Y-m-d') ?? '',

                default => data_get($pedido, $key, '—'),
            };
        }

        // Dinámicas (características del filtro)
        foreach ($this->columnasFiltro as $col) {
            $carId = (int)($col['id'] ?? 0);
            $vals  = $this->valoresPorPedidoYCar[$pedido->id][$carId] ?? [];
            $row[] = !empty($vals) ? collect($vals)->unique()->implode(', ') : ($col['fallback'] ?? '—');
        }

        return $row;
    }
}