<?php

namespace App\Livewire\Usuarios;

use App\Models\User;
use App\Models\Empresa;
use App\Models\Sucursal;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CrearUsuarios extends Component
{
    // --- Campos base ---
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    // --- Rol ---
    public string $role = '';
    public array  $rolesDisponibles = [];

    // --- Cliente principal: crear Empresa + Sucursal ---
    public ?string $empresa_nombre = null;
    public ?string $empresa_rfc = null;
    public ?string $empresa_telefono = null;
    public ?string $empresa_direccion = null;

    public ?string $sucursal_nombre = 'Matriz';
    public ?string $sucursal_telefono = null;
    public ?string $sucursal_direccion = null;

    // --- Cliente subordinado: búsqueda de Empresa + selección de Sucursal ---
    public string $empresaQuery = '';                 // ← entangle con x-model search
    public array  $empresasSugeridas = [];            // ← resultados para Alpine
    public bool   $puedeBuscarEmpresas = true;        // ← controla habilitar/deshabilitar buscador
    public ?int   $empresa_id_sub = null;             // ← entangle selectedId
    public $sucursalesDeEmpresa;                      // Collection
    public ?int   $sucursal_id_sub = null;

    public function mount(): void
    {
        $this->rolesDisponibles    = Role::pluck('name')->toArray();
        $this->empresasSugeridas   = [];
        $this->sucursalesDeEmpresa = collect();
        // Si quieres condicionar permisos de búsqueda:
        // $this->puedeBuscarEmpresas = auth()->user()?->hasRole('admin') ?? true;
    }

    protected function rules(): array
    {
        $base = [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role'     => ['required', Rule::in($this->rolesDisponibles)],
        ];

        if ($this->role === 'cliente_principal') {
            $base = array_merge($base, [
                'empresa_nombre'    => 'required|string|max:255',
                'empresa_rfc'       => 'nullable|string|max:20',
                'empresa_telefono'  => 'nullable|string|max:30',
                'empresa_direccion' => 'nullable|string|max:255',
                'sucursal_nombre'   => 'required|string|max:255',
                'sucursal_telefono' => 'nullable|string|max:30',
                'sucursal_direccion'=> 'nullable|string|max:255',
            ]);
        } elseif ($this->role === 'cliente_subordinado') {
            $base = array_merge($base, [
                'empresa_id_sub'  => 'required|exists:empresas,id',
                'sucursal_id_sub' => [
                    'required',
                    'exists:sucursales,id',
                    function ($attribute, $value, $fail) {
                        if ($this->empresa_id_sub && $value) {
                            $ok = Sucursal::where('id', $value)
                                ->where('empresa_id', $this->empresa_id_sub)
                                ->exists();
                            if (!$ok) {
                                $fail('La sucursal no pertenece a la empresa seleccionada.');
                            }
                        }
                    },
                ],
            ]);
        }

        return $base;
    }

    public function updated(string $propertyName): void
    {
        $this->validateOnly($propertyName);

        // Cascada: al cambiar la empresa seleccionada, recargar sucursales
        if ($propertyName === 'empresa_id_sub') {
            $this->cargarSucursalesEmpresa();
            $this->sucursal_id_sub = null;
        }
    }

    /**
     * Autocompletar de empresas: se dispara al escribir en $empresaQuery
     * (Gracias a wire:model.live en el input x-model="search")
     */
    public function updatedEmpresaQuery(): void
    {
        $term = trim($this->empresaQuery);

        if (!$this->puedeBuscarEmpresas || mb_strlen($term) < 2) {
            $this->empresasSugeridas = [];
            return;
        }

        $this->empresasSugeridas = Empresa::query()
            ->where(function ($q) use ($term) {
                $q->where('nombre', 'like', "%{$term}%")
                  ->orWhere('rfc', 'like', "%{$term}%");
            })
            ->orderBy('nombre')
            ->limit(20)
            ->get(['id', 'nombre', 'rfc'])
            ->map(fn ($e) => [
                'id'     => $e->id,
                'nombre' => $e->nombre,
                'rfc'    => $e->rfc,
            ])
            ->toArray();
    }

    protected function cargarSucursalesEmpresa(): void
    {
        $this->sucursalesDeEmpresa = $this->empresa_id_sub
            ? Sucursal::where('empresa_id', $this->empresa_id_sub)
                ->orderBy('tipo')     // 1 (Principal) primero
                ->orderBy('nombre')
                ->get()
            : collect();
    }

    public function createUser(): void
    {
        $this->validate();

        DB::transaction(function () {
            // 1) Crear usuario
            $user = User::create([
                'name'     => $this->name,
                'email'    => $this->email,
                'password' => Hash::make($this->password),
            ]);

            // 2) Rol
            $user->assignRole($this->role);
            Log::debug('Asignando rol', ['rol' => $this->role, 'user_id' => $user->id]);

            // 3) Flujo por rol
            if ($this->role === 'cliente_principal') {
                // Empresa
                $empresa = Empresa::create([
                    'nombre'    => (string) $this->empresa_nombre,
                    'rfc'       => $this->empresa_rfc,
                    'telefono'  => $this->empresa_telefono,
                    'direccion' => $this->empresa_direccion,
                ]);

                // Sucursal Principal
                $sucursal = Sucursal::create([
                    'empresa_id' => $empresa->id,
                    'nombre'     => $this->sucursal_nombre ?: 'Matriz',
                    'telefono'   => $this->sucursal_telefono,
                    'direccion'  => $this->sucursal_direccion,
                    'tipo'       => 1,
                ]);

                // Asignar
                $user->empresa_id  = $empresa->id;
                $user->sucursal_id = $sucursal->id;
                $user->save();

            } elseif ($this->role === 'cliente_subordinado') {
                // Asignar a empresa/sucursal existentes
                $user->empresa_id  = $this->empresa_id_sub;
                $user->sucursal_id = $this->sucursal_id_sub;
                $user->save();
            }
        });

        // Reset de formulario y listados
        $this->reset([
            'name','email','password','password_confirmation','role',
            'empresa_nombre','empresa_rfc','empresa_telefono','empresa_direccion',
            'sucursal_nombre','sucursal_telefono','sucursal_direccion',
            'empresaQuery','empresa_id_sub','sucursal_id_sub',
        ]);
        $this->empresasSugeridas   = [];
        $this->sucursalesDeEmpresa = collect();

        session()->flash('message', 'Usuario creado exitosamente.');
        $this->dispatch('notify', message: 'Usuario creado exitosamente');
    }

    public function render()
    {
        // Puedes calcular permisos de búsqueda aquí si lo necesitas dinámico
        $this->puedeBuscarEmpresas = true;

        return view('livewire.usuarios.crear-usuarios', [
            'puedeBuscarEmpresas' => $this->puedeBuscarEmpresas,
        ]);
    }
}
