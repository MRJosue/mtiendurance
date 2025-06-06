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
        Schema::create('pedido_tarea', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedido')->onDelete('cascade');
            $table->foreignId('tarea_produccion_id')->constrained('tareas_produccion')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_tarea');
    }
};
