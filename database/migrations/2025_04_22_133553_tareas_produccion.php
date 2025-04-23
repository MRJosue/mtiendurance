<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tareas_produccion', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('usuario_id');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('crete_user');
            $table->foreign('crete_user')->references('id')->on('users')->onDelete('cascade');
            
            $table->enum('tipo', ['DISEÑO', 'CORTE', 'BORDADO', 'PINTURA', 'FACTURACION', 'INDEFINIDA'])->default('INDEFINIDA');
            $table->string('descripcion')->nullable();
            $table->enum('estado', ['PENDIENTE', 'EN PROCESO', 'FINALIZADO', 'CANCELADO'])->default('PENDIENTE');
            $table->boolean('disenio_flag_first_proceso')->default(false); // opcional, si lo usas
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_fin')->nullable();
           
            $table->timestamps();



        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tareas_produccion');
    }
};