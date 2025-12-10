<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $table = 'chats';

    protected $fillable = [
        'proyecto_id',
        'fecha_creacion',
        'tipo_chat',     // 1 = cliente, 2 = proveedor
        'proveedor_id',  // user_id del "proveedor"
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'tipo_chat'      => 'integer',
    ];

    // ===== Relaciones =====

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function mensajes()
    {
        return $this->hasMany(MensajeChat::class, 'chat_id');
    }

    /**
     * Usuario con el que se abre el chat tipo proveedor.
     * (puede tener rol proveedor, staff, lo que sea)
     */
    public function proveedorUser()
    {
        return $this->belongsTo(User::class, 'proveedor_id');
    }

    // ===== Scopes útiles =====

    public function scopeCliente($query)
    {
        return $query->where('tipo_chat', 1);
    }

    public function scopeProveedor($query)
    {
        return $query->where('tipo_chat', 2);
    }

    public function scopeParaProveedorDeProyecto($query, $proyectoId, $userIdProveedor)
    {
        return $query->where('proyecto_id', $proyectoId)
            ->where('tipo_chat', 2)
            ->where('proveedor_id', $userIdProveedor);
    }
}
