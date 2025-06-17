<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    public function up()
    {
        // Asegurar que todos los valores en 'estado' sean válidos antes de cambiar la estructura
        DB::statement("
            UPDATE proyectos 
            SET estado = 'PENDIENTE' 
            WHERE estado NOT IN ('PENDIENTE', 'ASIGNADO', 'EN PROCESO', 'REVISION', 'DISEÑO APROBADO', 'DISEÑO RECHAZADO', 'CANCELADO')
        ");

        // Usar SQL para modificar el ENUM directamente
        DB::statement("
            ALTER TABLE proyectos 
            MODIFY estado ENUM(
                'PENDIENTE', 'ASIGNADO', 'EN PROCESO', 'REVISION', 'DISEÑO APROBADO', 'DISEÑO RECHAZADO', 'CANCELADO'
            ) DEFAULT 'PENDIENTE'
        ");
    }

    public function down()
    {
        // Asegurar valores válidos para revertir
        DB::statement("
            UPDATE proyectos 
            SET estado = 'PENDIENTE' 
            WHERE estado NOT IN ('PENDIENTE', 'ASIGNADO', 'REVISION', 'DISEÑO APROBADO')
        ");

        // Revertir ENUM al estado anterior (sin 'EN PROCESO' ni 'RECHAZADO')
        DB::statement("
            ALTER TABLE proyectos 
            MODIFY estado ENUM(
                'PENDIENTE', 'ASIGNADO', 'REVISION', 'DISEÑO APROBADO'
            ) DEFAULT 'PENDIENTE'
        ");
    }
};