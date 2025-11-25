<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sucursal extends Model
{
    use HasFactory; 

    protected $table = 'sucursales';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'telefono',
        'direccion',
        'tipo',
    ];

    /** Organización a la que pertenece la “empresa/sucursal” */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function getTipoLabelAttribute(): string
    {
        return (int) $this->tipo === 1 ? 'Principal' : 'Secundaria';
    }

    /** Clientes subordinados de esta “empresa” (según sucursal_id) */
    public function clientesSubordinados(): HasMany
    {
        return $this->hasMany(User::class, 'sucursal_id')
            ->where('rol', 'cliente_subordinado'); // si ya no usas columna rol, puedes ajustarlo
    }

    /** TODOS los usuarios asignados a esta “empresa” (sucursal) por sucursal_id */
    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'sucursal_id');
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }
}
