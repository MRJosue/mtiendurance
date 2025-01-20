<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DireccionEntrega extends Model
{
    use HasFactory;

    protected $table = 'direcciones_entrega';

    protected $fillable = [
        'user_id',
        'nombre_contacto',
        'calle',
        'ciudad',
        'estado',
        'codigo_postal',
        'telefono',
        'flag_default',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
