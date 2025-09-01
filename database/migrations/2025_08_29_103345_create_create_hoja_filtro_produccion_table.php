<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('hoja_filtro_produccion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hoja_id');
            $table->unsignedBigInteger('filtro_produccion_id');
            $table->integer('orden')->nullable();
            $table->timestamps();

            $table->foreign('hoja_id')->references('id')->on('hojas_filtros_produccion')->cascadeOnDelete();
            $table->foreign('filtro_produccion_id')->references('id')->on('filtros_produccion')->cascadeOnDelete();

            $table->unique(['hoja_id','filtro_produccion_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('hoja_filtro_produccion');
    }
};