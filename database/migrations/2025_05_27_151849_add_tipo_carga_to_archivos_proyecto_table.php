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
        Schema::table('archivos_proyecto', function (Blueprint $table) {
            $table->unsignedTinyInteger('tipo_carga')
                  ->default(1)
                  ->comment('Tipo de carga del archivo: valores del 1 al 9')
                  ->after('tipo_archivo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('archivos_proyecto', 'tipo_carga')) {
            Schema::table('archivos_proyecto', function (Blueprint $table) {
                $table->dropColumn('tipo_carga');
            });
        }
    }
};
