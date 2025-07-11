<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('tareas_produccion', function (Blueprint $table) {
            // Añadimos la relación con el paso de la orden
            $table->foreignId('orden_paso_id')
                  ->after('id')
                  ->constrained('orden_paso')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('tareas_produccion', function (Blueprint $table) {
            $table->dropForeign(['orden_paso_id']);
            $table->dropColumn('orden_paso_id');
        });
    }
};
