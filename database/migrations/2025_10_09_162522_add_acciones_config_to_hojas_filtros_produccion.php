<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hojas_filtros_produccion', function (Blueprint $table) {
            if (!Schema::hasColumn('hojas_filtros_produccion', 'acciones_config')) {
                $table->json('acciones_config')->nullable()->after('menu_config')
                    ->comment('Permisos de acciones visibles/habilitadas en HojaViewer');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hojas_filtros_produccion', function (Blueprint $table) {
            if (Schema::hasColumn('hojas_filtros_produccion', 'acciones_config')) {
                $table->dropColumn('acciones_config');
            }
        });
    }
};
