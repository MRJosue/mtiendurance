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
        Schema::create('proyecto_referencia', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('proyecto_id'); // Proyecto clonado
            $table->unsignedBigInteger('proyecto_origen_id'); // Proyecto original
            $table->timestamps();

            // Llaves foráneas
            $table->foreign('proyecto_id')->references('id')->on('proyectos')->onDelete('cascade');
            $table->foreign('proyecto_origen_id')->references('id')->on('proyectos')->onDelete('cascade');

            // Índice único para evitar duplicados
            $table->unique(['proyecto_id', 'proyecto_origen_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proyecto_referencia');
    }
};
