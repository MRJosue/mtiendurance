<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filtros_produccion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique()->nullable();
            $table->text('descripcion')->nullable();

            // Auditoría mínima (quién creó el filtro)
            $table->foreignId('created_by')->nullable()
                  ->constrained('users')->nullOnDelete();

            // Configuración visual y control interno
            $table->boolean('visible')->default(true);
            $table->integer('orden')->nullable();
            $table->json('config')->nullable(); // columnas extra, orden, anchos, etc.

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filtros_produccion');
    }
};
