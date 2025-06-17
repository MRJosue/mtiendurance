<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Asegurar valores válidos antes de cambiar el tipo
        DB::statement("
            UPDATE proyectos 
            SET estado = 'PENDIENTE' 
            WHERE estado NOT IN ('PENDIENTE', 'ASIGNADO', 'REVISION', 'DISEÑO APROBADO')
        ");

        // Modificar el ENUM directamente con SQL
        DB::statement("
            ALTER TABLE proyectos 
            MODIFY estado ENUM(
                'PENDIENTE', 'ASIGNADO', 'REVISION', 'DISEÑO APROBADO'
            ) DEFAULT 'PENDIENTE'
        ");
    }

    public function down()
    {
        // Asegurar valores válidos para revertir
        DB::statement("
            UPDATE proyectos 
            SET estado = 'PENDIENTE' 
            WHERE estado NOT IN (
                'PENDIENTE', 'APROBADO', 'PROGRAMADO', 'IMPRESIÓN', 'PRODUCCIÓN',
                'COSTURA', 'ENTREGA', 'FACTURACIÓN', 'COMPLETADO', 'RECHAZADO'
            )
        ");

        // Revertir el ENUM al estado anterior
        DB::statement("
            ALTER TABLE proyectos 
            MODIFY estado ENUM(
                'PENDIENTE', 'APROBADO', 'PROGRAMADO', 'IMPRESIÓN', 'PRODUCCIÓN',
                'COSTURA', 'ENTREGA', 'FACTURACIÓN', 'COMPLETADO', 'RECHAZADO'
            ) DEFAULT 'PENDIENTE'
        ");
    }
};