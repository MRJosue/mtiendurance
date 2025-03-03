<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupo_tallas_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_talla_id')
                ->constrained('grupos_tallas')
                ->onDelete('cascade')
                ->comment('Referencia al grupo de tallas');
            $table->foreignId('talla_id')
                ->constrained('tallas')
                ->onDelete('cascade')
                ->comment('Referencia a la talla');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupo_tallas_detalle');
    }
};
