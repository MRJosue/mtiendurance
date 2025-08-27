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
        Schema::create('filtro_produccion_caracteristicas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('filtro_produccion_id')
                  ->constrained('filtros_produccion')
                  ->cascadeOnDelete();

            $table->foreignId('caracteristica_id')
                  ->constrained('caracteristicas')
                  ->cascadeOnDelete();

            // Metadatos de presentación (layout de columna)
            $table->integer('orden')->nullable();
            $table->string('label')->nullable();
            $table->boolean('visible')->default(true);
            $table->string('ancho')->nullable(); // ej: "w-32" o "180px"
            $table->enum('render', ['texto','badges','chips','iconos','count'])->default('texto');
            $table->enum('multivalor_modo', ['inline','badges','count'])->default('inline');
            $table->unsignedTinyInteger('max_items')->default(4);
            $table->string('fallback')->nullable(); // que mostrar si no hay valor (ej: "—")

            $table->timestamps();

            $table->unique(['filtro_produccion_id','caracteristica_id'], 'ux_filtro_caracteristica');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filtro_produccion_caracteristicas');
    }
};
