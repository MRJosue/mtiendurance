<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class PreProyecto extends Model
{
    protected $table = 'pre_proyectos';

    protected $fillable = [
        'usuario_id', 
        'direccion_fiscal', 
        'direccion_fiscal_id',
        'direccion_entrega', 
        'direccion_entrega_id',
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

                // Crear el nuevo proyecto con los nuevos campos
                $proyecto = Proyecto::create([
                    'usuario_id' => $this->usuario_id,
                    // 'direccion_fiscal' => $this->direccion_fiscal,
                    // 'direccion_entrega' => $this->direccion_entrega,
                    // 'direccion_fiscal_id' => $this->direccion_fiscal_id,
                    // 'direccion_entrega_id' => $this->direccion_entrega_id,
                    'nombre' => $this->nombre,
                    'descripcion' => $this->descripcion,
                    //'id_tipo_envio' => $this->id_tipo_envio,
                    //'tipo' => $this->tipo,
                    'numero_muestras' => $this->numero_muestras,
                    //'estado' => 'PENDIENTE',
                    'fecha_creacion' => now(),
                    // 'fecha_produccion' => $this->fecha_produccion,
                    // 'fecha_embarque' => $this->fecha_embarque,
                    // 'fecha_entrega' => $this->fecha_entrega,
                    'categoria_sel' => $this->categoria_sel,
                    'producto_sel' => $this->producto_sel,
                    'caracteristicas_sel' => $this->caracteristicas_sel,
                    'opciones_sel' => $this->opciones_sel,
                    'total_piezas_sel' => $this->total_piezas_sel,
                ]);



                    // Crear el pedido asociado al proyecto
                    $categoria = json_decode($this->categoria_sel, true);
                    $producto = json_decode($this->producto_sel, true);
       
                    $caracteristicas = is_string($this->caracteristicas_sel) 
                                     ? json_decode($this->caracteristicas_sel, true) 
                                    : $this->caracteristicas_sel;


                                    $totalPiezas = is_string($this->total_piezas_sel) 
                                    ? json_decode($this->total_piezas_sel, true)  // Convertir a array asociativo
                                    : (is_object($this->total_piezas_sel) ? (array) $this->total_piezas_sel : $this->total_piezas_sel);
                                


                        // Verificar valor de detalle_tallas
                    Log::info("Valor de detalle_tallas", ['detalle_tallas' => $this->detalle_tallas]);
                    Log::info("Valor de total_piezas_sel", ['total_piezas_sel' => $this->total_piezas_sel]);
                    Log::info("Valor de totalPiezas", ['total_piezas' => $totalPiezas]);



                    $pedido = Pedido::create([
                        'proyecto_id' => $proyecto->id,
                        'pre_proyecto_id' => $this->id,
                        'producto_id' => $producto['id'] ?? null,
                        'user_id' => $this->usuario_id, // Si el cliente es el mismo usuario, ajusta esto según sea necesario
                        'cliente_id' => null, // Si el cliente es el mismo usuario, ajusta esto según sea necesario
                        'fecha_creacion' => now(),
                        'total' => $totalPiezas['total'] ?? 0,
                        'estatus' => 'PENDIENTE',

                        'direccion_fiscal_id'=>  $this->direccion_fiscal_id,
                        'direccion_fiscal'=> $this->direccion_fiscal,
                        'direccion_entrega_id'=> $this->direccion_entrega_id,
                        'direccion_entrega'=> $this->direccion_entrega,
                        'tipo'=> 'PEDIDO',
                        'estado'=> 'POR APROBAR',
                        'fecha_produccion'=> $this->fecha_produccion,
                        'fecha_embarque'=> $this->fecha_embarque,
                        'fecha_entrega'=> $this->fecha_entrega,
                        'id_tipo_envio' => $this->id_tipo_envio,
                    ]);


                    // Creamos el chat
                    Chat::create([
                        'proyecto_id'=>  $proyecto->id,
                    ]);


                    // Insertar características del pedido
                    if (!empty($caracteristicas)) {
                        foreach ($caracteristicas as $caracteristica) {
                            PedidoCaracteristica::create([
                                'pedido_id' => $pedido->id,
                                'caracteristica_id' => $caracteristica['id'],
                            ]);

                            // Insertar opciones de la característica
                            if (!empty($caracteristica['opciones'])) {
                                foreach ($caracteristica['opciones'] as $opcion) {
                                    PedidoOpcion::create([
                                        'pedido_id' => $pedido->id,
                                        'opcion_id' => $opcion['id'],
                                        'valor' => $opcion['valoru'] ?? null,
                                    ]);
                                }
                            }
                        }
                    }

                    // Si la categoría es "playeras", insertar tallas,
                    // isset($categoria['id']) && $categoria['id'] == 1 &&
                    if (isset($totalPiezas['detalle_tallas'])) {
                        foreach ($totalPiezas['detalle_tallas'] as $grupoId => $tallas) {
                            foreach ($tallas as $tallaId => $cantidad) {
                                if (!empty($cantidad)) {
                                    PedidoTalla::create([
                                        'pedido_id' => $pedido->id,
                                        'talla_id' => $tallaId,
                                        'grupo_talla_id' => $grupoId, // Ahora se captura el grupo correctamente
                                        'cantidad' => $cantidad,
                                    ]);
                                }
                            }
                        }
                    }

                // Transferir archivos relacionados con el preproyecto
                ArchivoProyecto::where('pre_proyecto_id', $this->id)->update([
                    'proyecto_id' => $proyecto->id,
                    'pre_proyecto_id' => null,
                ]);

                // Eliminar el preproyecto después de la transferencia
                $this->delete();

                return $proyecto;
        }


    // public function transferirAProyecto()
    // {
    //     // Crear el nuevo proyecto
    //     $proyecto = Proyecto::create([
    //         'usuario_id' => $this->usuario_id,
    //         'nombre' => $this->nombre,
    //         'descripcion' => $this->descripcion,
    //         'estado' => 'PENDIENTE',
    //         'fecha_creacion' => now(),
    //         'fecha_produccion' => $this->fecha_produccion,
    //         'fecha_embarque' => $this->fecha_embarque,
    //         'fecha_entrega' => $this->fecha_entrega,
    //     ]);

    //     // Transferir pedidos
    //     Pedido::where('pre_proyecto_id', $this->id)->update([
    //         'proyecto_id' => $proyecto->id,
    //         'pre_proyecto_id' => null,
    //     ]);

    //     // Transferir archivos
    //     ArchivoProyecto::where('pre_proyecto_id', $this->id)->update([
    //         'proyecto_id' => $proyecto->id,
    //         'pre_proyecto_id' => null,
    //     ]);

    //     // Eliminar el preproyecto
    //     $this->delete();

    //     return $proyecto;
    // }

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
