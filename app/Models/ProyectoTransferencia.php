<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProyectoTransferencia extends Model
{
    use HasFactory;

    protected $table = 'proyecto_transferencias';

    protected $fillable = [
        'proyecto_id',
        'owner_actual_id',
        'owner_nuevo_id',
        'solicitado_por_id',
        'aprobado_por_id',
        'estado',
        'motivo',
        'approved_at',
        'rejected_at',
        'applied_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'applied_at'  => 'datetime',
    ];

    /* ======================
     |  Relaciones
     ====================== */

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function ownerActual()
    {
        return $this->belongsTo(User::class, 'owner_actual_id');
    }

    public function ownerNuevo()
    {
        return $this->belongsTo(User::class, 'owner_nuevo_id');
    }

    public function solicitadoPor()
    {
        return $this->belongsTo(User::class, 'solicitado_por_id');
    }

    public function aprobadoPor()
    {
        return $this->belongsTo(User::class, 'aprobado_por_id');
    }

    /* ======================
     |  Scopes útiles
     ====================== */

    public function scopePendientes($query)
    {
        return $query->where('estado', 'PENDIENTE');
    }

    public function scopeActivas($query)
    {
        return $query->whereIn('estado', ['PENDIENTE', 'APROBADO']);
    }

    /* ======================
     |  Helpers de dominio
     ====================== */

    public function puedeAplicarse(): bool
    {
        return $this->estado === 'APROBADO' && is_null($this->applied_at);
    }
}
