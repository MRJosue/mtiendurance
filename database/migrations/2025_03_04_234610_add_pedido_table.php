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

            $table->unsignedBigInteger('direccion_fiscal_id')->nullable();
            $table->string('direccion_fiscal')->nullable();
            $table->unsignedBigInteger('direccion_entrega_id')->nullable();
            $table->string('direccion_entrega')->nullable();
            
            $table->bigInteger('id_tipo_envio')->comment('Guarda la referencia del tipo de envio');
            
            $table->enum('tipo', ['POR DEFINIR','PEDIDO', 'MUESTRA', ])->default('POR DEFINIR');
            $table->enum('estado', ['POR PROGRAMAR', 'PROGRAMADO',  'IMPRESIÓN', 'PRODUCCIÓN', 'COSTURA', 'ENTREGA', 'FACTURACIÓN', 'COMPLETADO', 'RECHAZADO'])->default('POR PROGRAMAR');
            $table->date('fecha_produccion')->nullable();
            $table->date('fecha_embarque')->nullable();
            $table->date('fecha_entrega')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            // Eliminando las columnas agregadas en up()
            $table->dropColumn([
                'direccion_fiscal_id',
                'direccion_fiscal',
                'direccion_entrega_id',
                'direccion_entrega',
                'fecha_produccion',
                'fecha_embarque',
                'fecha_entrega',
                'tipo',
                'estado'
            ]);


        });
    }
};
