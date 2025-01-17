<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// En esta migracion se agregan las columnas adicionales para auxiliar a la tabla de preproyectos
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->unsignedBigInteger('pre_proyecto_id')->nullable()->after('proyecto_id');
            $table->foreign('pre_proyecto_id')->references('id')->on('pre_proyectos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->dropForeign(['pre_proyecto_id']);
            $table->dropColumn('pre_proyecto_id');
        });
    }
};
