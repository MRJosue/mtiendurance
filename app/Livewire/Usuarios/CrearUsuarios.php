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
use Illuminate\Support\Facades\Auth;

class CrearUsuarios extends Component
{
    /** Tipo de usuario según entrada:
     *  1 → CLIENTE
     *  2 → PROVEEDOR
     *  3 → STAFF
     *  4 → ADMIN
     */
    public ?int $tipo = 1;

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
    public string $empresaQuery = '';
    public array  $empresasSugeridas = [];
    public bool   $puedeBuscarEmpresas = true;
    public ?int   $empresa_id_sub = null;
    public $sucursalesDeEmpresa; // Collection
    public ?int   $sucursal_id_sub = null;

    // --- STAFF: selección de Empresa + Sucursal ---
    public ?int $empresa_id_staff = null;
    public ?int $sucursal_id_staff = null;
    public $sucursalesStaff;   // Collection de sucursales para staff
    public $empresasStaff;     // Collection de empresas para staff

    /* ===================== MOUNT ===================== */

    public function mount(int $tipo = 1): void
    {
        // Normalizas el tipo
        $this->tipo = in_array($tipo, [1, 2, 3, 4], true) ? $tipo : 1;

        // 🔐 Revalidar permisos aquí también
        $user = Auth::user();

        $permisoPorTipo = [
            1 => 'usuarios.crear.cliente',
            2 => 'usuarios.crear.proveedor',
            3 => 'usuarios.crear.staff',
            4 => 'usuarios.crear.admin',
        ];

        $permisoNecesario = $permisoPorTipo[$this->tipo] ?? null;

        if (!$user || !$permisoNecesario || !$user->can($permisoNecesario)) {
            $this->redirectRoute('usuarios.index');
            return;
        }

        // Si llega aquí, sí tiene permiso → carga roles según tipo
        $this->cargarRolesDisponibles();

        // Inicializaciones
        $this->empresasSugeridas   = [];
        $this->sucursalesDeEmpresa = collect();

        $this->empresasStaff       = collect();
        $this->sucursalesStaff     = collect();

        // Si es STAFF, cargar empresas y preseleccionar MTIENDURANCE + sucursal principal
        if ($this->tipo === 3) {
            $this->empresasStaff = Empresa::orderBy('nombre')->get();
            $this->preseleccionarEmpresaSucursalStaffPorDefecto();
        }
    }

    /**
     * Carga los roles disponibles según el tipo de usuario que se va a crear.
     */
    protected function cargarRolesDisponibles(): void
    {
        $query = Role::query();

        switch ($this->tipo) {
            case 1: // CLIENTE
                $query->where('tipo', 1)
                      ->where('name', 'cliente_principal');
                break;

            case 2: // PROVEEDOR
                $query->where('tipo', 2);
                break;

            case 3: // STAFF
                $query->where('tipo', 3);
                break;

            case 4: // ADMIN
                $query->where('tipo', 4);
                break;

            default:
                $query->whereRaw('1 = 0');
                break;
        }

        $this->rolesDisponibles = $query
            ->orderBy('name')
            ->pluck('name')
            ->toArray();
    }

    /**
     * STAFF: intenta preseleccionar Empresa "MTIENDURANCE" (o "MTI Endurance")
     * y su sucursal principal.
     */
    protected function preseleccionarEmpresaSucursalStaffPorDefecto(): void
    {
        if ($this->empresa_id_staff) {
            // Ya hay algo seleccionado (por si se reutiliza el componente)
            $this->cargarSucursalesStaff();
            return;
        }

        $empresa = Empresa::query()
            ->where(function ($q) {
                $q->where('nombre', 'MTIENDURANCE')
                  ->orWhere('nombre', 'MTI Endurance');
            })
            ->orderByRaw("CASE WHEN nombre = 'MTIENDURANCE' THEN 0 ELSE 1 END")
            ->first();

        if (!$empresa) {
            // No existe la empresa MTIENDURANCE, simplemente no preseleccionamos
            return;
        }

        $this->empresa_id_staff = $empresa->id;

        $this->sucursalesStaff = Sucursal::where('empresa_id', $empresa->id)
            ->orderBy('tipo')   // 1 Principal primero
            ->orderBy('nombre')
            ->get();

        $sucursalPrincipal = $this->sucursalesStaff->firstWhere('tipo', 1)
            ?? $this->sucursalesStaff->first();

        if ($sucursalPrincipal) {
            $this->sucursal_id_staff = $sucursalPrincipal->id;
        }
    }

    /* ===================== VALIDACIÓN ===================== */

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

        // Validación extra para STAFF: empresa + sucursal obligatorias
        if ($this->tipo === 3) {
            $base = array_merge($base, [
                'empresa_id_staff'  => 'required|exists:empresas,id',
                'sucursal_id_staff' => [
                    'required',
                    'exists:sucursales,id',
                    function ($attribute, $value, $fail) {
                        if ($this->empresa_id_staff && $value) {
                            $ok = Sucursal::where('id', $value)
                                ->where('empresa_id', $this->empresa_id_staff)
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

        // Cascada: al cambiar la empresa seleccionada para cliente subordinado
        if ($propertyName === 'empresa_id_sub') {
            $this->cargarSucursalesEmpresa();
            $this->sucursal_id_sub = null;
        }

        // Cascada: al cambiar la empresa seleccionada para staff
        if ($propertyName === 'empresa_id_staff') {
            $this->cargarSucursalesStaff();
            $this->sucursal_id_staff = null;
        }
    }

    /* ===================== AUTOCOMPLETAR EMPRESAS (cliente subordinado) ===================== */

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
                ->orderBy('tipo')
                ->orderBy('nombre')
                ->get()
            : collect();
    }

    protected function cargarSucursalesStaff(): void
    {
        $this->sucursalesStaff = $this->empresa_id_staff
            ? Sucursal::where('empresa_id', $this->empresa_id_staff)
                ->orderBy('tipo')
                ->orderBy('nombre')
                ->get()
            : collect();
    }

    /* ===================== CREAR USUARIO ===================== */

    public function createUser(): void
    {
        $this->validate();

        DB::transaction(function () {
            // Determinar tipo a guardar en la tabla users
            $tipoUsuario = $this->tipo ?? 1;

            // 1) Crear usuario con tipo (1–4) según entrada
            $user = User::create([
                'name'     => $this->name,
                'email'    => $this->email,
                'password' => Hash::make($this->password),
                'tipo'     => $tipoUsuario,
            ]);

            // 2) Asignar rol Spatie
            $user->assignRole($this->role);
            Log::debug('Asignando rol', ['rol' => $this->role, 'user_id' => $user->id]);

            // 3) Flujo por rol / tipo
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

                // Asignar al usuario
                $user->empresa_id  = $empresa->id;
                $user->sucursal_id = $sucursal->id;
                $user->save();

            } elseif ($this->role === 'cliente_subordinado') {
                // Asignar a empresa/sucursal existentes
                $user->empresa_id  = $this->empresa_id_sub;
                $user->sucursal_id = $this->sucursal_id_sub;
                $user->save();

            } elseif ($this->tipo === 3 && $this->empresa_id_staff && $this->sucursal_id_staff) {
                // STAFF: asignar empresa + sucursal seleccionadas
                $user->empresa_id  = $this->empresa_id_staff;
                $user->sucursal_id = $this->sucursal_id_staff;
                $user->save();
            }
        });

        // Reset de formulario y listados
        $this->reset([
            'name','email','password','password_confirmation','role',
            'empresa_nombre','empresa_rfc','empresa_telefono','empresa_direccion',
            'sucursal_nombre','sucursal_telefono','sucursal_direccion',
            'empresaQuery','empresa_id_sub','sucursal_id_sub',
            'empresa_id_staff','sucursal_id_staff',
        ]);

        $this->empresasSugeridas   = [];
        $this->sucursalesDeEmpresa = collect();
        $this->empresasStaff       = collect();
        $this->sucursalesStaff     = collect();

        session()->flash('message', 'Usuario creado exitosamente.');
        $this->dispatch('notify', message: 'Usuario creado exitosamente');
    }

    /* ===================== RENDER ===================== */

    public function render()
    {
        // Aquí podrías recalcular permisos dinámicamente si quieres
        $this->puedeBuscarEmpresas = true;

        // Para staff, aseguramos tener la lista de empresas cargada
        if ($this->tipo === 3 && ($this->empresasStaff === null || $this->empresasStaff->isEmpty())) {
            $this->empresasStaff = Empresa::orderBy('nombre')->get();
        }

        return view('livewire.usuarios.crear-usuarios', [
            'puedeBuscarEmpresas' => $this->puedeBuscarEmpresas,
            'empresasStaff'       => $this->tipo === 3 ? $this->empresasStaff : collect(),
        ]);
    }
}
