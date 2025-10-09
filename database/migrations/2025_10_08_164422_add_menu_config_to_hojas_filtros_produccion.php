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
        Schema::table('hojas_filtros_produccion', function (Blueprint $table) {
            // Config de visibilidad en menús (ubicaciones, etiqueta, icono, orden, activo)
            $table->json('menu_config')->nullable()->after('base_columnas');
        });
    }

    public function down(): void
    {
        Schema::table('hojas_filtros_produccion', function (Blueprint $table) {
            $table->dropColumn('menu_config');
        });
    }
};
