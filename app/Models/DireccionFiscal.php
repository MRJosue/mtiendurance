<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DireccionFiscal extends Model
{
    use HasFactory;

    protected $table = 'direcciones_fiscales';

    protected $fillable = [
        'user_id',
        'rfc',
        'calle',
        'ciudad',
        'estado',
        'codigo_postal',
        'flag_default',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}