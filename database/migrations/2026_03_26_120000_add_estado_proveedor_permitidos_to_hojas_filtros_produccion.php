<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hojas_filtros_produccion', function (Blueprint $table) {
            if (!Schema::hasColumn('hojas_filtros_produccion', 'estado_proveedor_permitidos')) {
                $table->json('estado_proveedor_permitidos')
                    ->nullable()
                    ->after('estado_produccion_permitidos');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hojas_filtros_produccion', function (Blueprint $table) {
            if (Schema::hasColumn('hojas_filtros_produccion', 'estado_proveedor_permitidos')) {
                $table->dropColumn('estado_proveedor_permitidos');
            }
        });
    }
};
