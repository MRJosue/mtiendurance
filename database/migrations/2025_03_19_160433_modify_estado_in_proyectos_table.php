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
            WHERE estado NOT IN ('PENDIENTE', 'ASIGNADO', 'REVISION', 'DISEÑO APROBADO')
        ");

        Schema::table('proyectos', function (Blueprint $table) {
            // Modificar la columna estado para aceptar los nuevos valores
            $table->enum('estado', [
                'PENDIENTE', 'ASIGNADO', 'REVISION', 'DISEÑO APROBADO'
            ])->default('PENDIENTE')->change();
        });
    }

    public function down()
    {
        // Restaurar los valores anteriores
        DB::statement("
            UPDATE proyectos 
            SET estado = 'PENDIENTE' 
            WHERE estado NOT IN ('PENDIENTE', 'APROBADO', 'PROGRAMADO', 'IMPRESIÓN', 'PRODUCCIÓN',
                                'COSTURA', 'ENTREGA', 'FACTURACIÓN', 'COMPLETADO', 'RECHAZADO')
        ");

        Schema::table('proyectos', function (Blueprint $table) {
            // Restaurar los valores anteriores en la columna estado
            $table->enum('estado', [
                'PENDIENTE', 'APROBADO', 'PROGRAMADO', 'IMPRESIÓN', 'PRODUCCIÓN',
                'COSTURA', 'ENTREGA', 'FACTURACIÓN', 'COMPLETADO', 'RECHAZADO'
            ])->default('PENDIENTE')->change();
        });
    }
};
