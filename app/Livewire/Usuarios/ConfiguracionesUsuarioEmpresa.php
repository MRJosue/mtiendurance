<?php

namespace App\Livewire\Usuarios;


use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class ConfiguracionesUsuarioEmpresa extends Component
{
    use WithPagination;

    public $userId;

    public $search = '';
    public $perPage = 10;

    public $showModal = false;
    public $showDeleteModal = false;
    public $showUsuariosModal = false;

    public $empresaId = null;
    public $nombre, $rfc, $telefono, $direccion;
    public $usuariosSeleccionados = [];

    public $empresaAEliminar = null;
    public $alertaRelacionUsuarios = false;

    protected $queryString = ['search'];

    protected function rules()
    {
        return [
            'nombre' => 'required|string|max:255',
            'rfc' => [
                'required', 'string', 'max:20',
                Rule::unique('empresas', 'rfc')->ignore($this->empresaId),
            ],
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
        ];
    }

    public function mount($userId)
    {
        $this->userId = $userId;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // public function render()
    // {
    //     $empresas = Empresa::withCount(['clientesPrincipales'])
    //         ->when($this->search, fn($q) =>
    //             $q->where('nombre', 'like', '%'.$this->search.'%')
    //         )
    //         ->orderBy('nombre')
    //         ->paginate($this->perPage);

    //         $usuariosClientePrincipal = User::query()
    //             ->role('cliente_principal')
    //             ->get();
    //     return view('livewire.usuarios.configuraciones-usuario-empresa', [
    //         'empresas' => $empresas,
    //         'usuariosClientePrincipal' => $usuariosClientePrincipal,
    //     ]);
    // }

    public function render()
    {
        $user = User::find($this->userId);

        $empresasQuery = Empresa::withCount(['clientesPrincipales'])
            ->when($this->search, fn($q) =>
                $q->where('nombre', 'like', '%'.$this->search.'%')
            )
            ->orderBy('nombre');

        if (!$user->hasRole('admin')) {
            // Solo la empresa relacionada con el usuario (si tiene)
            $empresasQuery->where('id', $user->empresa_id);
        }

        $empresas = $empresasQuery->paginate($this->perPage);

        // $usuariosClientePrincipal = User::role('cliente_principal')->get();
        $usuariosClientePrincipal = User::query()->role('cliente_principal')->get();
        return view('livewire.usuarios.configuraciones-usuario-empresa', [
            'empresas' => $empresas,
            'usuariosClientePrincipal' => $usuariosClientePrincipal,
        ]);
    }

    public function nuevaEmpresa()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function editarEmpresa($id)
    {
        $empresa = Empresa::with('clientesPrincipales')->findOrFail($id);
        $this->empresaId = $empresa->id;
        $this->nombre = $empresa->nombre;
        $this->rfc = $empresa->rfc;
        $this->telefono = $empresa->telefono;
        $this->direccion = $empresa->direccion;
        $this->usuariosSeleccionados = $empresa->clientesPrincipales()->pluck('id')->toArray();
        $this->showModal = true;
    }

    public function guardarEmpresa()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $empresa = $this->empresaId
                ? Empresa::findOrFail($this->empresaId)
                : new Empresa();

            $empresa->fill([
                'nombre' => $this->nombre,
                'rfc' => $this->rfc,
                'telefono' => $this->telefono,
                'direccion' => $this->direccion,
            ]);
            $empresa->save();

            // Relación usuarios
            if ($this->empresaId) {
                // Desasignar usuarios actuales
                User::where('empresa_id', $empresa->id)
                    ->whereNotIn('id', $this->usuariosSeleccionados)
                    ->whereHas('roles', fn($q) => $q->where('name', 'cliente_principal'))
                    ->update(['empresa_id' => null]);
            }

            // Asignar los seleccionados
            User::whereIn('id', $this->usuariosSeleccionados)
                ->whereHas('roles', fn($q) => $q->where('name', 'cliente_principal'))
                ->update(['empresa_id' => $empresa->id]);

            DB::commit();
            $this->dispatch('notify', type: 'success', message: 'Empresa guardada correctamente.');
            $this->showModal = false;
            $this->resetForm();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', type: 'error', message: 'Error al guardar: '.$e->getMessage());
        }
    }

    public function confirmarEliminar($id)
    {
        $empresa = Empresa::withCount('clientesPrincipales')->findOrFail($id);
        if ($empresa->clientesPrincipales_count > 0) {
            $this->alertaRelacionUsuarios = true;
            $this->empresaAEliminar = $empresa->id;
            $this->showDeleteModal = true;
        } else {
            $this->alertaRelacionUsuarios = false;
            $this->empresaAEliminar = $empresa->id;
            $this->showDeleteModal = true;
        }
    }

    public function eliminarEmpresa()
    {
        $empresa = Empresa::withCount('clientesPrincipales')->findOrFail($this->empresaAEliminar);
        if ($empresa->clientesPrincipales_count > 0) {
            $this->dispatch('notify', type: 'error', message: 'No puedes eliminar una empresa con usuarios asignados.');
            $this->showDeleteModal = false;
            return;
        }

        $empresa->delete();
        $this->dispatch('notify', type: 'success', message: 'Empresa eliminada correctamente.');
        $this->showDeleteModal = false;
    }

    public function gestionarUsuarios($id)
    {
        $empresa = Empresa::with('clientesPrincipales')->findOrFail($id);
        $this->empresaId = $empresa->id;
        $this->usuariosSeleccionados = $empresa->clientesPrincipales()->pluck('id')->toArray();
        $this->showUsuariosModal = true;
    }

    public function guardarUsuarios()
    {
        // Desasignar usuarios que ya no estén
        User::where('empresa_id', $this->empresaId)
            ->whereNotIn('id', $this->usuariosSeleccionados)
            ->whereHas('roles', fn($q) => $q->where('name', 'cliente_principal'))
            ->update(['empresa_id' => null]);

        // Asignar seleccionados
        User::whereIn('id', $this->usuariosSeleccionados)
            ->whereHas('roles', fn($q) => $q->where('name', 'cliente_principal'))
            ->update(['empresa_id' => $this->empresaId]);

        $this->showUsuariosModal = false;
        $this->dispatch('notify', type: 'success', message: 'Usuarios actualizados correctamente.');
    }

    public function resetForm()
    {
        $this->empresaId = null;
        $this->nombre = null;
        $this->rfc = null;
        $this->telefono = null;
        $this->direccion = null;
        $this->usuariosSeleccionados = [];
    }
}



// use Livewire\Component;

// class ConfiguracionesUsuarioEmpresa extends Component
// {
//     public function render()
//     {
//         return view('livewire.usuarios.configuraciones-usuario-empresa');
//     }
// }
