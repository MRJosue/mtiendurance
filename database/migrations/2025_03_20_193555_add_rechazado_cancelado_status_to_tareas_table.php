<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Asegura que no haya valores inválidos antes del cambio
        DB::statement("
            UPDATE tareas 
            SET estado = 'PENDIENTE' 
            WHERE estado NOT IN ('PENDIENTE', 'EN PROCESO', 'COMPLETADA', 'RECHAZADO', 'CANCELADO')
        ");

        // Modifica el tipo ENUM directamente en SQL
        DB::statement("
            ALTER TABLE tareas 
            MODIFY estado ENUM(
                'PENDIENTE', 'EN PROCESO', 'COMPLETADA', 'RECHAZADO', 'CANCELADO'
            ) DEFAULT 'PENDIENTE'
        ");
    }

    public function down()
    {
        // Normaliza los datos antes de revertir el enum
        DB::statement("
            UPDATE tareas 
            SET estado = 'PENDIENTE' 
            WHERE estado NOT IN ('PENDIENTE', 'EN PROCESO', 'COMPLETADA')
        ");

        // Revertir el ENUM al estado anterior
        DB::statement("
            ALTER TABLE tareas 
            MODIFY estado ENUM(
                'PENDIENTE', 'EN PROCESO', 'COMPLETADA'
            ) DEFAULT 'PENDIENTE'
        ");
    }
};