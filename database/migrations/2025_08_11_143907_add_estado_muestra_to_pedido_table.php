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
            // Estatus exclusivo para pedidos de tipo MUESTRA
            $table->enum('estatus_muestra', [
                'PENDIENTE',
                'SOLICITADA',
                'MUESTRA LISTA',
                'ENTREGADA',
                'COMPLETADA',
                'CANCELADA',
            ])->nullable()->after('tipo')->default('PENDIENTE');;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            if (Schema::hasColumn('pedido', 'estatus_muestra')) {
                $table->dropColumn('estatus_muestra');
            }
        });
    }
};