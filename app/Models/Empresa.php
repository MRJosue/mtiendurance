<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Empresa extends Model
{
      protected $fillable = [
        'nombre',
        'rfc',
        'telefono',
        'direccion',
    ];

    public function sucursales(): HasMany
    {
        return $this->hasMany(Sucursal::class);
    }

    public function clientesPrincipales(): HasMany
    {
        return $this->hasMany(User::class, 'empresa_id')
            ->where('rol', 'cliente_principal');
    }
}
