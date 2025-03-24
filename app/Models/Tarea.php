<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class Tarea extends Model
{
    use HasFactory;

    protected $table = 'tareas';

    protected $fillable = [
        'proyecto_id',
        'staff_id',
        'descripcion',
        'estado',
        'disenio_flag_first_proceso',
    ];


    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }


    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }


    /**
     * Cambia el estado de la tarea con el ID especificado.
     *
     * @param int $tareaId El ID de la tarea a actualizar.
     * @param string $nuevoEstado El nuevo estado a asignar.
     * @return bool Devuelve `true` si el cambio fue exitoso, `false` si no.
     */
    public static function cambiarEstado($tareaId, $nuevoEstado)
    {
        // Definir los estados válidos
        $estadosValidos = ['PENDIENTE', 'EN PROCESO', 'COMPLETADA', 'RECHAZADO', 'CANCELADO'];

        // Validar si el estado especificado es válido
        if (!in_array($nuevoEstado, $estadosValidos)) {
            Log::warning("Intento de cambiar a un estado inválido", [
                'tarea_id' => $tareaId,
                'estado_intentado' => $nuevoEstado
            ]);
            return false;
        }

        // Buscar la tarea por ID
        $tarea = self::find($tareaId);

        // Si no se encuentra la tarea, registrar error y salir
        if (!$tarea) {
            Log::error("Tarea no encontrada para cambiar de estado", [
                'tarea_id' => $tareaId,
                'estado_intentado' => $nuevoEstado
            ]);
            return false;
        }

        // Si el estado es diferente al actual, proceder con el cambio
        if ($tarea->estado !== $nuevoEstado) {
            $estadoAnterior = $tarea->estado;
            $tarea->estado = $nuevoEstado;
            $tarea->save();

            Log::info("Estado de la tarea actualizado", [
                'tarea_id' => $tarea->id,
                'estado_anterior' => $estadoAnterior,
                'nuevo_estado' => $nuevoEstado,
                'fecha_cambio' => Carbon::now(),
            ]);

            return true;
        }

        // Si el estado ya era el mismo, no hacer nada
        return false;
    }
}
