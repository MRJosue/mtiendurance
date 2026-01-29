<?php

namespace App\Livewire;

use App\Models\Pedido;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;

use PowerComponents\LivewirePowerGrid\Column;

use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use App\Models\Proyecto;
use Livewire\Attributes\On;
use Illuminate\Support\HtmlString;


final class PedidosPowerGrid extends PowerGridComponent
{
    public string $tableName = 'pedidosPowerGridTable';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Pedido::query()
            ->with([
                'proyecto.user',
                'producto.categoria',
            ])
            ->where('tipo', 'PEDIDO');
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('proyecto_id')

            // ✅ CLAVE: proyecto_id-id con tooltip
            ->add('clave_html', function (Pedido $m) {
                $clave = ($m->proyecto_id ?? '—') . '-' . ($m->id ?? '—');

                // Usa tu accessor si existe; si no, cae a algo útil
                $tooltip = $m->tooltip_clave
                    ?? ($m->descripcion_pedido ?? 'Sin descripción');

                $tooltip = e($tooltip);

                return '<span class="font-semibold cursor-help" title="'.$tooltip.'">'.$clave.'</span>';
            })

            ->add('proyecto_nombre', fn (Pedido $m) => $m->proyecto?->nombre ?? '—')
            ->add('cliente_nombre', fn (Pedido $m) => $m->proyecto?->user?->name ?? '—')

            ->add('producto_categoria', function (Pedido $m) {
                $prod = $m->producto?->nombre ?? 'Sin producto';
                $cat  = $m->producto?->categoria?->nombre ?? 'Sin categoría';
                return '<div class="font-medium">'.e($prod).'</div><div class="text-xs text-gray-500">'.e($cat).'</div>';
            })

            ->add('total_piezas', fn (Pedido $m) => number_format((float)($m->total ?? 0), 0) . ' piezas')

            // ✅ CHIP ESTADO DISEÑO (proyecto->estado)
            ->add('estado_diseno_chip', function (Pedido $m) {
                $estado = strtoupper((string)($m->proyecto?->estado ?? ''));
                $map = [
                    'PENDIENTE'        => 'bg-yellow-400 text-black',
                    'ASIGNADO'         => 'bg-blue-500 text-white',
                    'EN PROCESO'       => 'bg-orange-500 text-white',
                    'REVISION'         => 'bg-purple-600 text-white',
                    'DISEÑO APROBADO'  => 'bg-emerald-600 text-white',
                    'DISEÑO RECHAZADO' => 'bg-red-600 text-white',
                    'CANCELADO'        => 'bg-gray-500 text-white',
                ];
                $class = $map[$estado] ?? 'bg-gray-200 text-gray-700';

                return new HtmlString('<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap min-w-[11rem] justify-center '.$class.'">'
                    . e($estado ?: '—') .
                '</span>');
            })

            // ✅ CHIP ESTADO PEDIDO (pedido->estado)
            ->add('estado_pedido_chip', function (Pedido $m) {
                $estado = strtoupper((string)($m->estado ?? ''));
                $class = match ($estado) {
                    'APROBADO'      => 'bg-emerald-600 text-white',
                    'ENTREGADO'     => 'bg-blue-600 text-white',
                    'RECHAZADO'     => 'bg-red-600 text-white',
                    'ARCHIVADO'     => 'bg-gray-600 text-white',
                    'PROGRAMADO'    => 'bg-indigo-600 text-white',
                    'POR PROGRAMAR' => 'bg-amber-500 text-black',
                    default         => 'bg-yellow-400 text-black',
                };

                return new HtmlString('<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap min-w-[9rem] justify-center '.$class.'">'
                    . e($estado ?: '—') .
                '</span>');
            })

            // ✅ Fechas seguras (si vienen null no truena)
            ->add('fecha_produccion_fmt', fn (Pedido $m) =>
                $m->fecha_produccion ? Carbon::parse($m->fecha_produccion)->format('Y-m-d') : 'No definida'
            )
            ->add('fecha_entrega_fmt', fn (Pedido $m) =>
                $m->fecha_entrega ? Carbon::parse($m->fecha_entrega)->format('Y-m-d') : 'No definida'
            );


            
    }

    // public function columns(): array
    // {
    //     return [
    //         Column::make('Id', 'id'),
    //         Column::make('Id', 'id'),
    //         Column::make('Proyecto id', 'proyecto_id'),
    //         Column::make('Producto id', 'producto_id'),
    //         Column::make('User id', 'user_id'),
    //         Column::make('Cliente id', 'cliente_id'),
    //         Column::make('Fecha creacion', 'fecha_creacion_formatted', 'fecha_creacion')
    //             ->sortable(),

    //         Column::make('Fecha creacion', 'fecha_creacion')
    //             ->sortable()
    //             ->searchable(),

    //         Column::make('Total', 'total')
    //             ->sortable()
    //             ->searchable(),

    //         Column::make('Total minutos', 'total_minutos')
    //             ->sortable()
    //             ->searchable(),

    //         Column::make('Total pasos', 'total_pasos')
    //             ->sortable()
    //             ->searchable(),

    //         Column::make('Resumen tiempos', 'resumen_tiempos')
    //             ->sortable()
    //             ->searchable(),

    //         Column::make('Estatus', 'estatus')
    //             ->sortable()
    //             ->searchable(),

    //         Column::make('Direccion fiscal id', 'direccion_fiscal_id'),
    //         Column::make('Direccion fiscal', 'direccion_fiscal')
    //             ->sortable()
    //             ->searchable(),

    //         Column::make('Direccion entrega id', 'direccion_entrega_id'),
    //         Column::make('Direccion entrega', 'direccion_entrega')
    //             ->sortable()
    //             ->searchable(),

    //         Column::make('Tipo', 'tipo')
    //             ->sortable()
    //             ->searchable(),

    //         Column::make('Estatus entrega muestra', 'estatus_entrega_muestra')
    //             ->sortable()
    //             ->searchable(),

    //         Column::make('Estatus muestra', 'estatus_muestra')
    //             ->sortable()
    //             ->searchable(),

    //         Column::make('Estado', 'estado')
    //             ->sortable()
    //             ->searchable(),

    //         Column::make('Estado id', 'estado_id'),
    //         Column::make('Estado produccion', 'estado_produccion')
    //             ->sortable()
    //             ->searchable(),

    //         Column::make('Fecha produccion', 'fecha_produccion_formatted', 'fecha_produccion')
    //             ->sortable(),

    //         Column::make('Fecha embarque', 'fecha_embarque_formatted', 'fecha_embarque')
    //             ->sortable(),

    //         Column::make('Fecha entrega', 'fecha_entrega_formatted', 'fecha_entrega')
    //             ->sortable(),

    //         Column::make('Id tipo envio', 'id_tipo_envio'),
    //         Column::make('Descripcion pedido', 'descripcion_pedido')
    //             ->sortable()
    //             ->searchable(),

    //         Column::make('Instrucciones muestra', 'instrucciones_muestra')
    //             ->sortable()
    //             ->searchable(),

    //         Column::make('Flag facturacion', 'flag_facturacion')
    //             ->sortable()
    //             ->searchable(),

    //         Column::make('Url', 'url')
    //             ->sortable()
    //             ->searchable(),

    //         Column::make('Last uploaded file id', 'last_uploaded_file_id'),
    //         Column::make('Flag aprobar sin fechas', 'flag_aprobar_sin_fechas')
    //             ->sortable()
    //             ->searchable(),

    //         Column::make('Flag solicitud aprobar sin fechas', 'flag_solicitud_aprobar_sin_fechas')
    //             ->sortable()
    //             ->searchable(),

    //         Column::make('Flag solicitud aprobar sin fechas', 'flag_solicitud_aprobar_sin_fechas')
    //             ->sortable()
    //             ->searchable(),

    //         Column::make('Ind activo', 'ind_activo')
    //             ->sortable()
    //             ->searchable(),

    //         Column::make('Estatus proveedor', 'estatus_proveedor')
    //             ->sortable()
    //             ->searchable(),

    //         Column::make('Proveedor visto at', 'proveedor_visto_at_formatted', 'proveedor_visto_at')
    //             ->sortable(),

    //         Column::make('Proveedor visto at', 'proveedor_visto_at')
    //             ->sortable()
    //             ->searchable(),

    //         Column::make('Proveedor visto por', 'proveedor_visto_por'),
    //         Column::make('Nota proveedor', 'nota_proveedor')
    //             ->sortable()
    //             ->searchable(),

    //         Column::make('Created at', 'created_at_formatted', 'created_at')
    //             ->sortable(),

    //         Column::make('Created at', 'created_at')
    //             ->sortable()
    //             ->searchable(),

    //         Column::action('Action')
    //     ];
    // }



    public function columns(): array
    {
        return [
            Column::make('ID', 'clave_html')
                ->sortable('id')
                ->searchable('id'),

            Column::make('Proyecto', 'proyecto_nombre')->searchable(),
            Column::make('Cliente', 'cliente_nombre')->searchable(),
            Column::make('Producto / Categoría', 'producto_categoria'),

            Column::make('Total', 'total_piezas')->sortable('total'),
            Column::make('Estado Diseño', 'estado_diseno_chip'),
            Column::make('Estado Pedido', 'estado_pedido_chip')->sortable('estado'),
            Column::make('Producción', 'fecha_produccion_fmt'),
            Column::make('Entrega', 'fecha_entrega_fmt')->sortable('fecha_entrega'),

            Column::action('Acciones'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::datepicker('fecha_produccion'),
            Filter::datepicker('fecha_embarque'),
            Filter::datepicker('fecha_entrega'),
        ];
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert('.$rowId.')');
    }

    public bool $modalVerInfo = false;
    public $infoProyecto = null;




    #[On('pg-ir-diseno')]
    public function irDiseno(int $proyectoId): void
    {
        if ($proyectoId <= 0) return;

        $url = route('proyecto.show', ['proyecto' => $proyectoId]);
        $this->js("window.location.href = " . json_encode($url) . ";");
    }


    #[On('pg-ver-info')]
    public function abrirModalVerInfoFromGrid(int $proyectoId): void
    {
        if ($proyectoId <= 0) return;
        $this->abrirModalVerInfo($proyectoId);
    }


    public function actions(Pedido $row): array
    {
        $proyectoId = (int) ($row->proyecto_id ?? 0);

        return [
            Button::add('ver-diseno')
                ->slot('🎨 Ver diseño')
                ->class('px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition')
                ->route('proyecto.show', ['proyecto' => $proyectoId]),

            Button::add('ver-info')
                ->slot('ℹ️ Ver info')
                ->class('px-3 py-2 text-sm bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition')
                ->dispatch('pg-ver-info', ['proyectoId' => $proyectoId]),
        ];
    }


    public function abrirModalVerInfo(int $proyectoId): void
    {
        $proyecto = Proyecto::with(['user', 'categoria'])->findOrFail($proyectoId);

        $proyecto->caracteristicas_sel = is_array($proyecto->caracteristicas_sel)
            ? $proyecto->caracteristicas_sel
            : json_decode($proyecto->caracteristicas_sel, true);

        $proyecto->producto_sel = is_array($proyecto->producto_sel)
            ? $proyecto->producto_sel
            : json_decode($proyecto->producto_sel, true);

        $proyecto->categoria_sel = is_array($proyecto->categoria_sel)
            ? $proyecto->categoria_sel
            : json_decode($proyecto->categoria_sel, true);

        $this->infoProyecto = $proyecto;
        $this->modalVerInfo = true;
    }

    public function closeModal(): void
    {
        $this->modalVerInfo = false;
        $this->infoProyecto = null;
    }
    /*
    public function actionRules($row): array
    {
       return [
            // Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn($row) => $row->id === 1)
                ->hide(),
        ];
    }
    */
}
