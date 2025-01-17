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
            $table->string('nombre')->nullable();
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['PENDIENTE', 'RECHAZADO'])->default('PENDIENTE');
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->date('fecha_produccion')->nullable();
            $table->date('fecha_embarque')->nullable();
            $table->date('fecha_entrega')->nullable();
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
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
