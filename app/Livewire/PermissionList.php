<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;

class PermissionList extends Component
{
    use WithPagination;

    public $search = '';

    protected $paginationTheme = 'bootstrap'; // Si usas Bootstrap para paginación

    public function render()
    {
        $permissions = Permission::query()
            ->where('name', 'like', '%' . $this->search . '%')
            ->paginate(10);

        return view('livewire.permission-list', compact('permissions'));
    }
}
