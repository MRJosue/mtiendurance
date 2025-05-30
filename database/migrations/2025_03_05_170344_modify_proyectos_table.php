<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // **Primero aseguramos que los valores en 'estado' sean válidos**
        DB::statement("
            UPDATE proyectos 
            SET estado = 'PENDIENTE' 
            WHERE estado NOT IN ('PENDIENTE', 'APROBADO', 'PROGRAMADO', 'IMPRESIÓN', 'PRODUCCIÓN', 'COSTURA', 'ENTREGA', 'FACTURACIÓN', 'COMPLETADO', 'RECHAZADO')
        ");

        Schema::table('proyectos', function (Blueprint $table) {
            // Modificando la columna estado
            $table->enum('estado', [
                'PENDIENTE', 'APROBADO', 'PROGRAMADO', 'IMPRESIÓN', 'PRODUCCIÓN',
                'COSTURA', 'ENTREGA', 'FACTURACIÓN', 'COMPLETADO', 'RECHAZADO'
            ])->default('PENDIENTE')->change();
        });
    }

    public function down()
    {
        Schema::table('proyectos', function (Blueprint $table) {
            // Restaurando la columna estado a su versión anterior (si es necesario)
            $table->enum('estado', [
                'PENDIENTE', 'ASIGNADO', 'REVISION', 'DISEÑO APROBADO'
            ])->default('PENDIENTE')->change();
        });
    }
};