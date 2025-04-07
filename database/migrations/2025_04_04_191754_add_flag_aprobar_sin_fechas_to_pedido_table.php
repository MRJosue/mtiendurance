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
        Schema::table('pedido', function (Blueprint $table) {
            $table->tinyInteger('flag_aprobar_sin_fechas')
                  ->default(0)
                  ->comment('1: permite aprobar el pedido sin fechas, 0: requiere fechas')
                  ->after('estado_produccion'); // Ajusta la posición si lo necesitas
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->dropColumn('flag_aprobar_sin_fechas');
        });
    }
};
