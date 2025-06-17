<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// En esta migracion se agregan las columnas adicionales para auxiliar a la tabla de preproyectos, adicional mente se agrega una columna de descripcion
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('archivos_proyecto', function (Blueprint $table) {

            $table->text('descripcion')->nullable();
         
                $table->unsignedBigInteger('pre_proyecto_id')->nullable()->after('proyecto_id');
                $table->foreign('pre_proyecto_id')->references('id')->on('pre_proyectos')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('archivos_proyecto', 'pre_proyecto_id')) {
            Schema::table('archivos_proyecto', function (Blueprint $table) {
                $table->dropForeign(['pre_proyecto_id']);
                $table->dropColumn('pre_proyecto_id');
            });
        }

    }
};
