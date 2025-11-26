<?php

namespace App\Livewire\Usuarios;

use Livewire\Component;
use App\Models\User;
use App\Models\Sucursal;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class ConfiguracionesUsuarioMakeuser extends Component
{
    public $userId;
    public $jefe;
    public $subordinados = [];

    // Permisos/flags
    public $canAdmin = false;

    // Form
    public $showForm = false;
    public $editingId = null;
    public $name, $email, $password;
    public $sucursal_id = null;
    public $sucursales;          // colección para el select
    public $nameLocked = false;  // bloquear edición de nombre si no-admin
    public $sucursalLocked = false; // bloquear edición de sucursal si no-admin

    protected function rules()
    {
        $rules = [
            'name' => ['required','string','max:255'],
            'email' => [
                'required','email','max:255',
                Rule::unique('users')->ignore($this->editingId)
            ],
            'sucursal_id' => ['nullable','exists:sucursales,id'],
        ];
        if (!$this->editingId) {
            $rules['password'] = ['required','string','min:6'];
        }
        return $rules;
    }

    public function mount($userId)
    {
        $this->userId = $userId;
        $this->sucursales = collect();

        // Spatie: sólo admin puede editar nombre y sucursal
        $this->canAdmin = auth()->check() && auth()->user()->hasRole('admin');

        $this->loadJefe();
        $this->loadSubordinados();
    }

    public function loadJefe()
    {
        $this->jefe = User::findOrFail($this->userId);
    }

    public function loadSubordinados()
    {
        $ids = $this->jefe->subordinados ?? [];

        $this->subordinados = User::whereIn('id', $ids)
            ->where('empresa_id', $this->jefe->empresa_id) // SOLO misma empresa del jefe
            ->with(['sucursal:id,nombre,tipo'])
            ->get();
    }

    protected function loadSucursalesForSelect(?int $empresaId): void
    {
        if (!$empresaId) { $this->sucursales = collect(); return; }

        $this->sucursales = Sucursal::where('empresa_id', $empresaId)
            ->orderByDesc('tipo')     // primero Principal
            ->orderBy('nombre')
            ->get(['id','nombre','tipo']);
    }

    public function showCreateForm()
    {
        if (!$this->jefe->empresa_id) {
            $this->dispatch('notify', type: 'error', message: 'El usuario no tiene organización asignada.');
            return;
        }

        $this->resetForm();

        // Sucursales SOLO de la empresa del jefe
        $this->loadSucursalesForSelect($this->jefe->empresa_id);

        // En creación no bloqueamos campos
        $this->nameLocked = false;
        $this->sucursalLocked = false;

        $this->showForm = true;
    }


    public function showEditForm($id)
    {
        $idsSubordinados = $this->jefe->subordinados ?? [];

        $user = User::where('id', $id)
            ->where('empresa_id', $this->jefe->empresa_id) // misma empresa
            ->whereIn('id', $idsSubordinados)              // tiene que ser subordinado de este jefe
            ->firstOrFail();

        $this->editingId   = $user->id;
        $this->name        = $user->name;
        $this->email       = $user->email;
        $this->password    = '';
        $this->sucursal_id = $user->sucursal_id;

        // Bloquea si el autenticado NO es admin
        $this->nameLocked     = !$this->canAdmin;
        $this->sucursalLocked = !$this->canAdmin;

        // Sucursales según empresa del jefe
        $this->loadSucursalesForSelect($this->jefe->empresa_id);

        $this->showForm = true;
    }

    public function saveUser()
    {
        $data = $this->validate();

        // Regla de negocio: la sucursal (si se envía) debe pertenecer a la empresa del jefe
        if ($this->sucursal_id) {
            $ok = Sucursal::where('id', $this->sucursal_id)
                ->where('empresa_id', $this->jefe->empresa_id)
                ->exists();
            if (!$ok) {
                $this->addError('sucursal_id', 'La sucursal seleccionada no pertenece a tu empresa.');
                return;
            }
        }

        if ($this->editingId) {
            // UPDATE: solo usuarios subordinados de la empresa del jefe
            $idsSubordinados = $this->jefe->subordinados ?? [];

            $user = User::where('id', $this->editingId)
                ->where('empresa_id', $this->jefe->empresa_id)
                ->whereIn('id', $idsSubordinados)
                ->firstOrFail();

            DB::transaction(function () use ($user) {
                if ($this->canAdmin) {
                    $user->name        = $this->name;
                    $user->sucursal_id = $this->sucursal_id ?: null;
                }

                $user->email = $this->email;

                if ($this->password) {
                    $user->password = Hash::make($this->password);
                }

                // Asegura empresa del subordinado = empresa del jefe
                $user->empresa_id = $this->jefe->empresa_id;

                $user->save();
            });

        } else {
            // CREATE: siempre misma empresa que el jefe
            if (!$this->jefe->empresa_id) {
                $this->dispatch('notify', type: 'error', message: 'El usuario no tiene organización asignada.');
                return;
            }

            DB::transaction(function () {
                $user = User::create([
                    'name'       => $this->name,
                    'email'      => $this->email,
                    'password'   => Hash::make($this->password),
                    'empresa_id' => $this->jefe->empresa_id,
                    'sucursal_id'=> $this->sucursal_id ?: null,
                ]);

                $subs = $this->jefe->subordinados ?? [];
                $subs[] = $user->id;
                $this->jefe->subordinados = array_values(array_unique($subs));
                $this->jefe->save();
            });
        }

        $this->loadJefe();
        $this->loadSubordinados();
        $this->showForm = false;
        $this->resetForm();

        $this->dispatch('notify', type: 'success', message: 'Usuario guardado correctamente');
        $this->dispatch('refreshMakeuser');
    }

    public function deleteUser($id)
    {
        $idsSubordinados = $this->jefe->subordinados ?? [];

        $user = User::where('id', $id)
            ->where('empresa_id', $this->jefe->empresa_id)
            ->whereIn('id', $idsSubordinados)
            ->firstOrFail();

        DB::transaction(function () use ($user) {
            $user->delete();

            $subs = $this->jefe->subordinados ?? [];
            $subs = array_values(array_diff($subs, [$user->id]));
            $this->jefe->subordinados = $subs;
            $this->jefe->save();
        });

        $this->loadJefe();
        $this->loadSubordinados();
        $this->dispatch('notify', type: 'success', message: 'Usuario eliminado correctamente');
    }


    public function resetForm()
    {
        $this->editingId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->sucursal_id = null;
        $this->nameLocked = false;
        $this->sucursalLocked = false;
    }

    public function render()
    {
        return view('livewire.usuarios.configuraciones-usuario-makeuser');
    }

    #[On('refreshMakeuser')]
    public function refreshMakeuser()
    {
        $this->loadJefe();
        $this->loadSubordinados();
        $this->dispatch('notify', message: 'Lista de subordinados actualizada');
    }
}
