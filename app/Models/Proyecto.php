<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Proyecto extends Model
{
    use HasFactory;

    protected $table = 'proyectos';
    protected $primaryKey = 'id';
    public $incrementing = true; // El campo ID no es auto-incremental
    protected $keyType = 'int'; // Tipo del campo ID como string

    protected $fillable = [
        'usuario_id',
        'nombre',
        'descripcion',
        'estado',
        'fecha_creacion',
        'fecha_entrega',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }


    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }
}
