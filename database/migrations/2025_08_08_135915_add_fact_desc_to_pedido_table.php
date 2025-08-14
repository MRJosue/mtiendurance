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
            // Descripción general del pedido
            $table->text('descripcion_pedido')
                  ->nullable()
                  ->after('estatus');

            // Instrucciones específicas para la muestra
            $table->text('instrucciones_muestra')
                  ->nullable()
                  ->after('descripcion_pedido');

            // Flag de facturación: 0 = no factura, 1 = sí factura
            $table->tinyInteger('flag_facturacion')
                  ->default(1)
                  ->comment('0: no se hace factura; 1: se hace factura')
                  ->after('instrucciones_muestra');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->dropColumn('flag_facturacion');
            $table->dropColumn('instrucciones_muestra');
            $table->dropColumn('descripcion_pedido');
        });
    }
};
