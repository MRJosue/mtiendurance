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
            $table->boolean('flag_descarga')
                  ->default(false)
                  ->comment('Indica si el archivo ha sido descargado')
                  ->after('tipo_carga');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('archivos_proyecto', function (Blueprint $table) {
            if (Schema::hasColumn('archivos_proyecto', 'flag_descarga')) {
                $table->dropColumn('flag_descarga');
            }
        });
    }
};
