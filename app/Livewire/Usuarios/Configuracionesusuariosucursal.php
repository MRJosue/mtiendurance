<?php


namespace App\Livewire\Usuarios;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Empresa;

class Configuracionesusuariosucursal extends Component
{
    use WithPagination;

    public $empresa_id, $nombre, $telefono, $direccion, $editingId = null;
    public $showUserModal = false;
    public $selectedSucursal = null;
    public $selectedUsers = [];
    public $search = '';
    public $userId;

    public $usuariosDisponibles = [];


    protected $rules = [
        'empresa_id' => 'required|exists:empresas,id',
        'nombre' => 'required|string|max:255',
        'telefono' => 'nullable|string|max:30',
        'direccion' => 'nullable|string|max:255',
    ];


    
    public function mount($userId)
    {
        $this->userId = $userId;

        $user = User::find($userId);
        $ids = $user->subordinados ?? [];

        $this->usuariosDisponibles = User::whereIn('id', $ids)->orderBy('name')->get();
    }

    // public function render()
    // {
    //     $sucursales = Sucursal::with(['empresa', 'usuarios'])
    //         ->where('nombre', 'like', "%{$this->search}%")
    //         ->orderBy('id', 'desc')
    //         ->paginate(10);

    //     $empresas = Empresa::orderBy('nombre')->get();

    //     return view('livewire.usuarios.configuracionesusuariosucursal', [
    //         'sucursales' => $sucursales,
    //         'empresas' => $empresas,
    //     ]);
    // }
    
    public function render()
    {
        $user = User::find($this->userId);

        // Empieza la query de sucursales
        $sucursalesQuery = Sucursal::with(['empresa', 'usuarios'])
            ->where('nombre', 'like', "%{$this->search}%")
            ->orderBy('id', 'desc');

        $empresasQuery = Empresa::orderBy('nombre');

        // Si el usuario no es admin, solo mostrar sucursales de su empresa
        if (!$user->hasRole('admin')) {
            if ($user->empresa_id) {
                $sucursalesQuery->where('empresa_id', $user->empresa_id);
                $empresasQuery->where('id', $user->empresa_id);
            } else {
                // Si no tiene empresa asignada, no muestra nada
                $sucursalesQuery->whereRaw('0=1');
                $empresasQuery->whereRaw('0=1');
            }
        }

        $sucursales = $sucursalesQuery->paginate(10);
        $empresas = $empresasQuery->get();

        return view('livewire.usuarios.configuraciones-usuario-sucursal', [
            'sucursales' => $sucursales,
            'empresas' => $empresas,
        ]);
    }

    public function resetInput()
    {
        $this->empresa_id = null;
        $this->nombre = '';
        $this->telefono = '';
        $this->direccion = '';
        $this->editingId = null;
    }

    public function create()
    {
        $this->resetInput();
    }

    public function store()
    {
        $this->validate();

        Sucursal::create([
            'empresa_id' => $this->empresa_id,
            'nombre' => $this->nombre,
            'telefono' => $this->telefono,
            'direccion' => $this->direccion,
        ]);

        $this->resetInput();
        $this->dispatch('notify', 'Sucursal creada correctamente');
    }

    public function edit($id)
    {
        $sucursal = Sucursal::findOrFail($id);
        $this->editingId = $sucursal->id;
        $this->empresa_id = $sucursal->empresa_id;
        $this->nombre = $sucursal->nombre;
        $this->telefono = $sucursal->telefono;
        $this->direccion = $sucursal->direccion;
    }

    public function update()
    {
        $this->validate();

        $sucursal = Sucursal::findOrFail($this->editingId);
        $sucursal->update([
            'empresa_id' => $this->empresa_id,
            'nombre' => $this->nombre,
            'telefono' => $this->telefono,
            'direccion' => $this->direccion,
        ]);

        $this->resetInput();
        $this->dispatch('notify', 'Sucursal actualizada correctamente');
    }

    public function delete($id)
    {
        Sucursal::destroy($id);
        $this->dispatch('notify', 'Sucursal eliminada');
    }

    public function openUserModal($sucursalId)
    {
        $user = User::find($this->userId);
        $ids = $user->subordinados ?? [];
        $this->usuariosDisponibles = User::whereIn('id', $ids)->orderBy('name')->get();

        $this->selectedSucursal = Sucursal::with('usuarios')->findOrFail($sucursalId);
        $this->selectedUsers = $this->selectedSucursal->usuarios()->pluck('users.id')->toArray();
        $this->showUserModal = true;
    }

    public function saveUsersToSucursal()
    {
        $this->selectedSucursal->usuarios()->sync($this->selectedUsers);
        $this->showUserModal = false;
        $this->dispatch('notify', 'Usuarios asignados a la sucursal');
    }

    public function closeUserModal()
    {
        $this->showUserModal = false;
        $this->selectedSucursal = null;
        $this->selectedUsers = [];
    }
}


// namespace App\Livewire\Usuarios;

// use Livewire\Component;

// class Configuracionesusuariosucursal extends Component
// {
//     public function render()
//     {
//         return view('livewire.usuarios.configuracionesusuariosucursal');
//     }
// }
