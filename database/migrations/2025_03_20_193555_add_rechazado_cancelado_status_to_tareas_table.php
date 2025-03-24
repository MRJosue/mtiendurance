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
            UPDATE tareas 
            SET estado = 'PENDIENTE' 
            WHERE estado NOT IN ('PENDIENTE', 'EN PROCESO', 'COMPLETADA', 'RECHAZADO', 'CANCELADO')
        ");

        Schema::table('tareas', function (Blueprint $table) {
            // Modificar la columna estado para incluir "RECHAZADO" y "CANCELADO"
            $table->enum('estado', [
                'PENDIENTE', 'EN PROCESO', 'COMPLETADA', 'RECHAZADO', 'CANCELADO'
            ])->default('PENDIENTE')->change();
        });
    }

    public function down()
    {
        // Restaurar los valores anteriores, eliminando "RECHAZADO" y "CANCELADO"
        DB::statement("
            UPDATE tareas 
            SET estado = 'PENDIENTE' 
            WHERE estado NOT IN ('PENDIENTE', 'EN PROCESO', 'COMPLETADA')
        ");

        Schema::table('tareas', function (Blueprint $table) {
            $table->enum('estado', [
                'PENDIENTE', 'EN PROCESO', 'COMPLETADA'
            ])->default('PENDIENTE')->change();
        });
    }
};
