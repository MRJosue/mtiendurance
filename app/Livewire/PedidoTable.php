<?php

namespace App\Livewire;

use App\Models\Pedido;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class PedidoTable extends PowerGridComponent
{
    public string $tableName = 'pedidoTable';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput(),

            PowerGrid::footer()
                ->showPerPage(perPage: 10, perPageValues: [10, 20, 30, 50, 100])
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        // Ajusta aquí si quieres filtrar solo activos:
        // return Pedido::query()->where('ind_activo', 1);

        return Pedido::query();
    }

    public function relationSearch(): array
    {
        // Cuando quieras buscar por relaciones (proyecto, producto, usuario, cliente)
        // se configura aquí.
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')

            ->add('proyecto_id')
            ->add('producto_id')
            ->add('user_id')
            ->add('cliente_id')

            ->add('tipo')
            ->add('estatus')
            ->add('estado_produccion')
            ->add('estatus_proveedor')
            ->add('ind_activo')

            ->add('total')

            ->add('fecha_produccion_formatted', fn (Pedido $m) => $this->formatDate($m->fecha_produccion))
            ->add('fecha_embarque_formatted', fn (Pedido $m) => $this->formatDate($m->fecha_embarque))
            ->add('fecha_entrega_formatted', fn (Pedido $m) => $this->formatDate($m->fecha_entrega))

            ->add('created_at_formatted', fn (Pedido $m) => $this->formatDateTime($m->created_at));
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->sortable()
                ->searchable(),

            Column::make('Proyecto', 'proyecto_id')
                ->sortable()
                ->searchable(),

            Column::make('Producto', 'producto_id')
                ->sortable()
                ->searchable(),

            Column::make('Usuario', 'user_id')
                ->sortable()
                ->searchable(),

            Column::make('Cliente', 'cliente_id')
                ->sortable()
                ->searchable(),

            Column::make('Tipo', 'tipo')
                ->sortable()
                ->searchable(),

            Column::make('Estatus', 'estatus')
                ->sortable()
                ->searchable(),

            Column::make('Producción', 'estado_produccion')
                ->sortable()
                ->searchable(),

            Column::make('Proveedor', 'estatus_proveedor')
                ->sortable()
                ->searchable(),

            Column::make('Activo', 'ind_activo')
                ->toggleable() // permite ocultar/mostrar columna desde UI si lo tienes habilitado
                ->sortable(),

            Column::make('Total', 'total')
                ->sortable()
                ->searchable(),

            Column::make('F. Producción', 'fecha_produccion_formatted', 'fecha_produccion')
                ->sortable(),

            Column::make('F. Embarque', 'fecha_embarque_formatted', 'fecha_embarque')
                ->sortable(),

            Column::make('F. Entrega', 'fecha_entrega_formatted', 'fecha_entrega')
                ->sortable(),

            Column::make('Creado', 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::action('Acciones'),
        ];
    }

    public function filters(): array
    {
        return [
            // Fechas
            Filter::datepicker('fecha_produccion'),
            Filter::datepicker('fecha_embarque'),
            Filter::datepicker('fecha_entrega'),

            // Básicos
            Filter::inputText('estatus')->operators(['contains']),
            Filter::inputText('tipo')->operators(['contains']),
            Filter::inputText('estado_produccion')->operators(['contains']),
        ];
    }

    public function actions(Pedido $row): array
    {
        return [
            Button::add('ver')
                ->slot('Ver')
                ->id()
                ->class('px-3 py-1.5 rounded-lg bg-blue-500 text-white text-xs hover:bg-blue-600')
                ->dispatch('openPedido', ['pedidoId' => $row->id]),

            Button::add('editar')
                ->slot('Editar')
                ->id()
                ->class('px-3 py-1.5 rounded-lg bg-gray-100 text-gray-800 text-xs hover:bg-gray-200')
                ->dispatch('editPedido', ['pedidoId' => $row->id]),
        ];
    }

    // Listeners (si quieres manejarlo desde el mismo componente)
    #[\Livewire\Attributes\On('editPedido')]
    public function editPedido(int $pedidoId): void
    {
        // Aquí NO uses alert en prod; mejor abre modal o redirige.
        // Ejemplo: $this->dispatch('open-modal', ...);
        $this->dispatch('notify', message: "Editar pedido #{$pedidoId}");
    }

    private function formatDate($value): string
    {
        if (blank($value)) {
            return '—';
        }

        return Carbon::parse($value)->format('d/m/Y');
    }

    private function formatDateTime($value): string
    {
        if (blank($value)) {
            return '—';
        }

        return Carbon::parse($value)->format('d/m/Y H:i');
    }
}
