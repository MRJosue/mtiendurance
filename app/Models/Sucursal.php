<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function getTipoLabelAttribute(): string
    {
        return (int) $this->tipo === 1 ? 'Principal' : 'Secundaria';
    }

    public function clientesSubordinados(): HasMany
    {
        return $this->hasMany(User::class, 'sucursal_id')
            ->where('rol', 'cliente_subordinado');
    }

    // Solo si usas tabla pivote sucursal_user
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'sucursal_user');
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }
}
