<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hojas_filtros_produccion', function (Blueprint $table) {
            if (!Schema::hasColumn('hojas_filtros_produccion', 'estado_produccion_permitidos')) {
                $table->json('estado_produccion_permitidos')->nullable()->after('estados_diseno_permitidos');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hojas_filtros_produccion', function (Blueprint $table) {
            if (Schema::hasColumn('hojas_filtros_produccion', 'estado_produccion_permitidos')) {
                $table->dropColumn('estado_produccion_permitidos');
            }
        });
    }
};