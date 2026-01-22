<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;



// para los roles
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;




/**
 * @method \Illuminate\Database\Eloquent\Collection getRoleNames()
 * @method \Illuminate\Database\Eloquent\Collection getAllPermissions()
 * @method bool hasRole(string|array $roles)
 * @method bool hasPermissionTo(string|array $permission)
 */

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles; 
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    
    const TIPOS = [
        1 => 'CLIENTE',
        2 => 'PROVEEDOR',
        3 => 'STAFF',
        4 => 'ADMIN',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'tipo', 
        'config',
        'user_can_sel_preproyectos',
        'user_legacy',
        'company_legacy',
        'super_legacy',
        'super_id_legacy',
        'empresa_id',
        'sucursal_id',
        'es_propietario',   
        'ind_activo',
        'flag_perfil_configurado'

    ];




    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'config' => 'array',
        'user_can_sel_preproyectos' => 'array',
        'subordinados' => 'array',
        'es_propietario' => 'boolean', 
        'flag_perfil_configurado' => 'boolean'
    ];






    //     /**
    //  * La relación con el modelo Role.
    public function role()
    {
        return $this->belongsTo(Role::class, 'rol_id');
    }

    
        /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'), // Contraseña genérica
        ];
    }



    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'usuario_id'); // Ajusta el campo si es necesario
    }


    public function getFlag(string $key, $default = false): bool
    {
        return boolval($this->config[$key] ?? $default);
    }

    public function setFlag(string $key, bool $value): void
    {
        $this->config = array_merge($this->config ?? [], [$key => $value]);
        $this->save();
    }


    // Para cliente principal
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    // Para cliente subordinado
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    // Si usas la tabla pivote sucursal_user
    public function sucursales(): BelongsToMany
    {
        return $this->belongsToMany(Sucursal::class, 'sucursal_user');
    }



    // ...
    protected $appends = [
        'tooltip_sucursal_empresa', // opcional: para acceder directo como atributo
    ];

    // Nombre legible de la sucursal (si existe)
    public function getSucursalNombreAttribute(): ?string
    {
        return $this->sucursal->nombre ?? null;
    }

    // Nombre de la empresa principal (por empresa directa o la de la sucursal)
    public function getEmpresaPrincipalNombreAttribute(): ?string
    {
        if ($this->empresa && $this->empresa->nombre) {
            return $this->empresa->nombre;
        }
        return $this->sucursal->empresa->nombre ?? null;
    }

    /**
     * Tooltip genérico para tablas.
     * Formato: "Sucursal: {Sucursal} — Empresa: {Empresa}"
     */
    public function getTooltipSucursalEmpresaAttribute(): string
    {
        $sucursal = $this->sucursal_nombre ?? 'Sin Empresa';
        $empresa  = $this->empresa_principal_nombre ?? 'Sin Organización Principal';
        return "Empresa: {$sucursal} — O.Principal: {$empresa}";
    }


        public function esTipoUsuario(int|string $tipo): bool
    {
        // Permite validar por número o texto
        if (is_numeric($tipo)) {
            return (int)$this->tipo === (int)$tipo;
        }

        return strtolower(self::TIPOS[$this->tipo] ?? '') === strtolower($tipo);
    }
    public function rolTipo(): ?int
    {
        // rol directo
        if (!empty($this->rol_id)) {
            return (int) \DB::table('roles')->where('id', $this->rol_id)->value('tipo');
        }

        // roles spatie
        return (int) \DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', $this::class)
            ->where('model_has_roles.model_id', $this->id)
            ->orderByDesc('roles.tipo')
            ->value('roles.tipo'); // toma uno (si tu sistema solo usa 1 rol real)
    }

    public function esStaffOAdmin(): bool
    {
        return in_array((int) $this->rolTipo(), [3, 4], true);
    }
    

    public function getTipoTextoAttribute(): string
    {
        return self::TIPOS[$this->tipo] ?? 'DESCONOCIDO';
    }

}
