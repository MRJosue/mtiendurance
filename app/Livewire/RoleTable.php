<?php

namespace App\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\Role;

class RoleTable extends DataTableComponent
{
    protected $model = Role::class;

    public function configure(): void
    {
        $this->setPrimaryKey('id');
    }

    public function columns(): array
    {
        return [
            Column::make("Id", "id")
                ->sortable(),
            Column::make("Name", "name")
                ->sortable(),
            Column::make("Control interno", "guard_name")
                ->sortable(),

            Column::make('Acciones')->label(fn($row)=>view('tables.actions',['row' => $row,'component'=>'assign-permissions-to-role','titulo'=>'Editar Permisos', 'wireKey' => "row-{$row->id}" ])),

        ];
    }
}
