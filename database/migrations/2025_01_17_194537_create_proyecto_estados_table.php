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
        Schema::create('proyecto_estados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('proyecto_id'); // Relación con la tabla proyectos
            $table->string('estado'); // Nombre del estado
            $table->timestamp('fecha_inicio')->nullable(); // Fecha de inicio del estado
            $table->timestamp('fecha_fin')->nullable(); // Fecha de finalización del estado
            $table->unsignedBigInteger('usuario_id'); // Usuario responsable del estado
            $table->timestamps();

            // Llaves foráneas
            $table->foreign('proyecto_id')->references('id')->on('proyectos')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade'); // Relación con la tabla users
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proyecto_estados');
    }
};
