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
        Schema::create('ordenes_produccion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable();
            $table->enum('tipo', ['CORTE', 'DISEÑO', 'BORDADO', 'PINTURA', 'FACTURACION', 'GENERAL'])->default('GENERAL');
            $table->enum('estado', ['PENDIENTE', 'EN PROCESO', 'FINALIZADO', 'CANCELADO'])->default('PENDIENTE');
           
            $table->unsignedBigInteger('usuario_id');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('crete_user');
            $table->foreign('crete_user')->references('id')->on('users')->onDelete('cascade');
            
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_fin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordenes_produccion');
    }
};
