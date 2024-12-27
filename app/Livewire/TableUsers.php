<?php

namespace App\Livewire;

use Livewire\WithPagination;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\User;


class TableUsers extends DataTableComponent
{
    use WithPagination;

    public array $selected = [];

    public function configure(): void
    {
        $this->setPrimaryKey('id');
        $this->setBulkActions([
            'exportSelected' => 'Exportar seleccionados',
            'deleteSelected' => 'Eliminar seleccionados',
        ]);
    }

    public function columns(): array
    {
        return [
            // Column::make('Seleccionar')
            //     ->checkbox(),

            Column::make('ID', 'id')
                ->sortable(),

            Column::make('Nombre', 'name')
                ->searchable()
                ->sortable(),

            Column::make('Correo Electrónico', 'email')
                ->searchable()
                ->sortable(),

            Column::make('Fecha de Creación', 'created_at')
                ->sortable()
                ->format(fn ($value) => $value->format('d/m/Y')),
        ];
    }

    public function query()
    {
        return User::query();
    }

    public function exportSelected()
    {
        $users = User::whereIn('id', $this->selected)->get();

        // Lógica para exportar $users

        $this->dispatchBrowserEvent('notification', ['message' => 'Usuarios exportados correctamente.']);
    }

    public function deleteSelected()
    {
        User::whereIn('id', $this->selected)->delete();
        $this->reset('selected');
        $this->dispatchBrowserEvent('notification', ['message' => 'Usuarios eliminados correctamente.']);
    }
}
