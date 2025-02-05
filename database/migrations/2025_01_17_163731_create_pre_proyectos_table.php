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
        Schema::create('pre_proyectos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->string('direccion_fiscal')->nullable();
            $table->string('direccion_entrega')->nullable();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->bigInteger('id_tipo_envio')->comment('Guarda la referencia del tipo de envio');
            $table->enum('tipo', ['PROYECTO', 'MUESTRA'])->default('PROYECTO');
            $table->integer('numero_muestras')->default(0);
            $table->enum('estado', ['PENDIENTE', 'RECHAZADO'])->default('PENDIENTE');
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->date('fecha_produccion')->nullable();
            $table->date('fecha_embarque')->nullable();
            $table->date('fecha_entrega')->nullable();
            $table->json('categoria_sel')->nullable();
            $table->json('producto_sel')->nullable();
            $table->json('caracteristicas_sel')->nullable();
            $table->json('opciones_sel')->nullable();
            $table->json('total_piezas_sel')->nullable()->comment('Guarda el total de piezas');
            $table->foreign('usuario_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_proyectos');
    }
};
