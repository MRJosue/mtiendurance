<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filtro_produccion_productos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('filtro_produccion_id')
                  ->constrained('filtros_produccion')
                  ->cascadeOnDelete();

            $table->foreignId('producto_id')
                  ->constrained('productos')
                  ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['filtro_produccion_id', 'producto_id'], 'ux_filtro_producto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filtro_produccion_productos');
    }
};
