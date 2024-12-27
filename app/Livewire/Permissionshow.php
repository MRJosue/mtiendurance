<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
class Permissionshow extends Component
{

    use WithPagination;

    public $search = '';

    protected $paginationTheme = 'bootstrap'; // Si usas Bootstrap para paginación

    public function render()
    {
        $permissions = Permission::query()
            ->where('name', 'like', '%' . $this->search . '%')
            ->paginate(10);

        return view('livewire.permissionshow', compact('permissions'));
    }


}
