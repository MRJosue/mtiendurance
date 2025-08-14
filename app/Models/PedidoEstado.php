<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PedidoEstado extends Model
{
    protected $table = 'pedido_estados';

    protected $fillable = [
        'pedido_id',
        'proyecto_id',
        'usuario_id',
        'estado',
        'comentario',
        'fecha_inicio',
        'fecha_fin',
    ];


    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function cambiarEstado(string $nuevoEstado)
    {

        // $tarea = TareaProduccion::find($id);
        // $tarea->cambiarEstado('EN PROCESO');
        // Validar estado permitido
        // $estadosPermitidos = ['PENDIENTE', 'EN PROCESO', 'COMPLETADA', 'RECHAZADO', 'CANCELADO',];
        // if (!in_array($nuevoEstado, $estadosPermitidos)) {
        //     throw new \InvalidArgumentException("Estado no permitido: $nuevoEstado");
        // }

        // Cambiar el estado de la tarea
        $this->estado = $nuevoEstado;
        $this->save();

        // Crear registro en pedido_estados
        PedidoEstado::create([
            'pedido_id' => $this->pedido_id,
            'proyecto_id' => $this->pedido->proyecto_id,
            'usuario_id' => Auth::id(),
            'estado' => "$nuevoEstado",
        ]);
    }
}