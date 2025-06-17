<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            // Agregar campo tinyInteger para marcar si es el primer proceso de diseño
            $table->tinyInteger('disenio_flag_first_proceso')->default(0)->after('estado')->comment('Marca si es el primer proceso de diseño (0 = No, 1 = Sí)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('tareas', 'disenio_flag_first_proceso')) {
            Schema::table('tareas', function (Blueprint $table) {
                // Eliminar la columna si se revierte la migración
                $table->dropColumn('disenio_flag_first_proceso');
            });
        }
    }
};
