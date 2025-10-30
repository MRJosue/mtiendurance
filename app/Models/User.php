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
    protected $fillable = [
        'name',
        'email',
        'password',
        'config',
        'user_can_sel_preproyectos',
        'user_legacy',
        'company_legacy',
        'super_legacy',
        'super_id_legacy',
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


    public function esTipoUsuario(string $tipo): bool
    {
        return $this->tipo_usuario === $tipo;
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
}
