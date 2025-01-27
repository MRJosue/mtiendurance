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
        Schema::create('caracteristica_opcion', function (Blueprint $table) {
            $table->id();
            $table->boolean('restriccion');
            $table->foreignId('caracteristica_id')->constrained('caracteristicas')->cascadeOnDelete();
            $table->foreignId('opcion_id')->constrained('opciones')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caracteristica_opcion');
    }
};
