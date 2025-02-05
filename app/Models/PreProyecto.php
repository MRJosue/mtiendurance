<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreProyecto extends Model
{
    protected $table = 'pre_proyectos';

    protected $fillable = [
        'usuario_id', 
        'direccion_fiscal', 
        'direccion_entrega', 
        'nombre', 
        'descripcion',
        'id_tipo_envio',
        'tipo', 
        'numero_muestras', 
        'estado', 
        'fecha_produccion', 
        'fecha_embarque',
        'fecha_entrega', 
        'categoria_sel', 
        'producto_sel', 
        'caracteristicas_sel', 
        'opciones_sel',
        'total_piezas_sel'
    ];

    protected $casts = [
        'fecha_produccion' => 'date',
        'fecha_embarque' => 'date',
        'fecha_entrega' => 'date',
        'categoria_sel' => 'json',
        'producto_sel' => 'json',
        'caracteristicas_sel' => 'json',
        'opciones_sel' => 'json',
        'total_piezas_sel' => 'json'
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

    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }



    public function setDireccionConcentrada($direccionFiscal, $direccionEntrega)
    {
        $this->direccion_concentrada = "{$direccionFiscal->nombre_contacto}, {$direccionFiscal->calle} | " .
                                       "{$direccionEntrega->nombre_contacto}, {$direccionEntrega->calle}";
    }


    public function caracteristicas()
    {
        return $this->belongsToMany(Caracteristica::class, 'producto_caracteristica');
    }

    public function opciones()
    {
        return $this->belongsToMany(Opcion::class, 'caracteristica_opcion');
    }
    
}
