<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;



class Empresa extends Model
{
    use HasFactory;  // ← Debe existir este “use”

    protected $fillable = [
        'nombre',
        'rfc',
        'telefono',
        'direccion',
    ];

    public function sucursales()
    {
        return $this->hasMany(Sucursal::class);
    }

    public function clientesPrincipales()
    {
        return $this->hasMany(User::class, 'empresa_id')
                    ->where('rol', 'cliente_principal');
    }
}