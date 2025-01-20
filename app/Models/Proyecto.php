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
        'direccion_fiscal',
        'direccion_entrega',
        'nombre',
        'descripcion',
        'tipo',
        'numero_muestras',
        'estado',
        'fecha_creacion',
        'fecha_produccion',
        'fecha_embarque',
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

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'proyecto_id');

    }

    public function archivos()
    {
        return $this->hasMany(ArchivoProyecto::class, 'proyecto_id');
    }

    public function chat()
    {
        return $this->hasOne(Chat::class, 'proyecto_id');
    }

    public function proyectoOrigen()
    {
        return $this->hasOne(Proyecto_Referencia::class, 'proyecto_id');
    }

    public function proyectosClonados()
    {
        return $this->hasMany(Proyecto_Referencia::class, 'proyecto_origen_id');
    }


}
