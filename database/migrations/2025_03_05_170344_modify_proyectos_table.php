<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
public function up()
{
    // Normaliza los valores actuales
    DB::statement("
        UPDATE proyectos 
        SET estado = 'PENDIENTE' 
        WHERE estado NOT IN ('PENDIENTE', 'APROBADO', 'PROGRAMADO', 'IMPRESIÓN', 'PRODUCCIÓN', 'COSTURA', 'ENTREGA', 'FACTURACIÓN', 'COMPLETADO', 'RECHAZADO')
    ");

    // Usa SQL puro para modificar la columna ENUM
    DB::statement("
        ALTER TABLE proyectos 
        MODIFY estado ENUM(
            'PENDIENTE', 'APROBADO', 'PROGRAMADO', 'IMPRESIÓN', 'PRODUCCIÓN',
            'COSTURA', 'ENTREGA', 'FACTURACIÓN', 'COMPLETADO', 'RECHAZADO'
        ) DEFAULT 'PENDIENTE'
    ");
}

public function down()
{
    DB::statement("
        ALTER TABLE proyectos 
        MODIFY estado ENUM(
            'PENDIENTE', 'ASIGNADO', 'REVISION', 'DISEÑO APROBADO'
        ) DEFAULT 'PENDIENTE'
    ");
}
};