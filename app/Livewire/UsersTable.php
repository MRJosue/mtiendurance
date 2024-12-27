<?php

namespace App\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\User;
use Rappasoft\LaravelLivewireTables\Views\Columns\ButtonGroupColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\LinkColumn;


use Livewire\WithPagination;

class UsersTable extends DataTableComponent
{
    use WithPagination;
    protected $model = User::class;
    public $showModal = false;
    public $userId;



    public function configure(): void
    {
        $this->setPrimaryKey('id');



        $this->setConfigurableAreas([
            'tools.toolbar.items.column-select' => false,  // Desactiva la opción de agregar/quitar columnas
        ]);
    }



    public function columns(): array
    {
        return [



            Column::make("Id", "id")
                ->sortable(),
            Column::make("Name", "name")
                ->sortable() ->searchable(),
            Column::make("Email", "email")
                ->sortable(),

            Column::make('Acciones')->label(fn($row)=>view('tables.actions',['row' => $row,'component'=>'assign-roles','titulo'=>'Editar Permisos', 'wireKey' => "row-{$row->id}" ])),


        ];
    }
}

// Column::make('Acciones')->label(fn($row)=>view('tables.actions',['row' => $row,'component'=>'assign-roles','titulo'=>'Editar Permisos'])),
// HtmlColumn::make('Actions')->html(fn($row) => '<button wire:click="$emit(\'openModal\', ' . $row->id . ')" class="btn btn-primary">Open Modal</button>'),
// Column::make('Acciones')->label(fn($row)=>view('tables.actions',['row' => $row,'component'=>'assign-roles','titulo'=>'Editar Permisos','showModal'=>true])),
// Column::make("Id", "id")->format(function ($value, $column, $row) {return view('Components.table-actions', ['rowId' => $row->id]);}),
//Column::make("Id", "id")->format(function ($row) {return view('tables.actions',['row' => $row,'component'=>'assign-roles','titulo'=>'Editar Permisos', 'wireKey' => "row-{$row->id}" ]);}),
