<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DireccionFiscal extends Model
{
    use HasFactory;

    protected $table = 'direcciones_fiscales';
    protected $fillable = [
        'usuario_id',
        'razon_social', // <-- NUEVO
        'rfc',
        'calle',
        'ciudad_id',
        'estado_id',
        'pais_id',
        'codigo_postal',
        'flag_default',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ciudad()
    {
        return $this->belongsTo(Ciudad::class);
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }

    public function pais()
    {
        return $this->belongsTo(Pais::class);
    }
}