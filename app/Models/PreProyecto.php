<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreProyecto extends Model
{
    protected $fillable = [
        'usuario_id', 
        'nombre', 
        'descripcion', 
        'estado', 
        'fecha_creacion', 
        'fecha_produccion',
        'fecha_embarque',
        'fecha_entrega',
    ];

    public function transferirAProyecto()
    {
        // Crear el nuevo proyecto
        $proyecto = Proyecto::create([
            'usuario_id' => $this->usuario_id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'estado' => 'PENDIENTE',
            'fecha_creacion' => now(),
            'fecha_produccion' => $this->fecha_produccion,
            'fecha_embarque' => $this->fecha_embarque,
            'fecha_entrega' => $this->fecha_entrega,
        ]);

        // Transferir pedidos
        Pedido::where('pre_proyecto_id', $this->id)->update([
            'proyecto_id' => $proyecto->id,
            'pre_proyecto_id' => null,
        ]);

        // Transferir archivos
        ArchivoProyecto::where('pre_proyecto_id', $this->id)->update([
            'proyecto_id' => $proyecto->id,
            'pre_proyecto_id' => null,
        ]);

        // Eliminar el preproyecto
        $this->delete();

        return $proyecto;
    }
}
