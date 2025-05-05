<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class LayoutElemento extends Model
{
    use HasFactory;

    protected $fillable = [
        'layout_id',
        'tipo',
        'letra',
        'caracteristica_id',
        'posicion_x',
        'posicion_y',
        'ancho',
        'alto',
        'orden',
        'configuracion',
    ];

    protected $casts = [
        'configuracion' => 'array',
    ];

    public function layout()
    {
        return $this->belongsTo(Layout::class);
    }

    public function caracteristica()
    {
        return $this->belongsTo(Caracteristica::class);
    }


}