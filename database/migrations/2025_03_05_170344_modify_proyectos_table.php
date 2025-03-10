<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('proyectos', function (Blueprint $table) {
            // Eliminando columnas innecesarias
            $table->dropColumn([
                'direccion_fiscal_id',
                'direccion_fiscal',
                'direccion_entrega_id',
                'direccion_entrega',
                'tipo',
                'fecha_produccion',
                'fecha_embarque',
                'fecha_entrega',
                 'id_tipo_envio'
            ]);

            // Modificando la columna estado
            $table->enum('estado', [
                'PENDIENTE', 'ASIGNADO', 'REVISION', 'DISEÑO APROBADO'
            ])->default('PENDIENTE')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('proyectos', function (Blueprint $table) {
            // Restaurando las columnas eliminadas
            $table->unsignedBigInteger('direccion_fiscal_id')->nullable();
            $table->string('direccion_fiscal')->nullable();
            $table->unsignedBigInteger('direccion_entrega_id')->nullable();
            $table->string('direccion_entrega')->nullable();
            $table->enum('tipo', ['PROYECTO', 'MUESTRA'])->default('PROYECTO');
            $table->date('fecha_produccion')->nullable();
            $table->date('fecha_embarque')->nullable();
            $table->date('fecha_entrega')->nullable();

            // Restaurando la columna estado a su versión anterior
            $table->enum('estado', [
                'PENDIENTE', 'APROBADO', 'PROGRAMADO', 'IMPRESIÓN', 'PRODUCCIÓN', 
                'COSTURA', 'ENTREGA', 'FACTURACIÓN', 'COMPLETADO', 'RECHAZADO'
            ])->default('PENDIENTE')->change();
        });
    }
};
