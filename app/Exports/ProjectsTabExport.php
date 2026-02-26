<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProjectsTabExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
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
        $user = Auth::user();

        $cols = ['ID', 'Nombre', 'Estado Proyecto', 'Estado Diseño'];

        // Cliente visible solo en roles que tú ya usas en tabla
        if ($user->hasAnyRole(['admin','estaf','jefediseñador','cliente_principal'])) {
            $cols[] = 'Cliente';
        }

        // Proveedor si tiene permiso
        if ($user->can('tablaProyectos-ver-columna-proveedor')) {
            $cols[] = 'Proveedor';
        }

        $cols[] = 'Creado';

        return $cols;
    }

    public function map($project): array
    {
        $user = Auth::user();

        $row = [
            $project->id,
            $project->nombre,
            $project->ind_activo ? 'Activo' : 'Inactivo',
            $project->estado ?? 'Sin estado',
        ];

        if ($user->hasAnyRole(['admin','estaf','jefediseñador','cliente_principal'])) {
            $row[] = $project->user?->name ?? 'Sin Cliente';
        }

        if ($user->can('tablaProyectos-ver-columna-proveedor')) {
            $row[] = $project->proveedor?->name ?? 'Sin proveedor';
        }

        $row[] = optional($project->created_at)->format('Y-m-d H:i');

        return $row;
    }
}
