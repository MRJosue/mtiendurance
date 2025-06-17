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

            $table->enum('estado', ['POR APROBAR', 'APROBADO',  'ENTREGADO', 'RECHAZADO', 'ARCHIVADO','POR REPROGRAMAR'])->default('POR APROBAR');
            
            $table->enum('estado_produccion', ['POR APROBAR','POR PROGRAMAR', 'PROGRAMADO',  'IMPRESIÓN', 'CORTE', 'COSTURA', 'ENTREGA', 'FACTURACIÓN', 'COMPLETADO', 'RECHAZADO'])->default('POR APROBAR');
            

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

            if (Schema::hasColumn('pedido', 'direccion_fiscal_id')) {
                $table->dropColumn('direccion_fiscal_id');
            }

            if (Schema::hasColumn('pedido', 'direccion_fiscal')) {
                $table->dropColumn('direccion_fiscal');
            }

            if (Schema::hasColumn('pedido', 'direccion_entrega_id')) {
                $table->dropColumn('direccion_entrega_id');
            }

            if (Schema::hasColumn('pedido', 'direccion_entrega')) {
                $table->dropColumn('direccion_entrega');
            }

            if (Schema::hasColumn('pedido', 'fecha_produccion')) {
                $table->dropColumn('fecha_produccion');
            }

            if (Schema::hasColumn('pedido', 'fecha_embarque')) {
                $table->dropColumn('fecha_embarque');
            }

            if (Schema::hasColumn('pedido', 'fecha_entrega')) {
                $table->dropColumn('fecha_entrega');
            }

            if (Schema::hasColumn('pedido', 'tipo')) {
                $table->dropColumn('tipo');
            }

            if (Schema::hasColumn('pedido', 'estado')) {
                $table->dropColumn('estado');
            }

            if (Schema::hasColumn('pedido', 'estado_produccion')) {
                $table->dropColumn('estado_produccion');
            }
        });
    }

};
