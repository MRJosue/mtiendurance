<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('layout_elementos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('layout_id');
            $table->string('tipo'); // texto, imagen, opcion, caracteristica
            $table->unsignedBigInteger('caracteristica_id')->nullable(); // Opcional según tipo
            $table->integer('posicion_x')->default(0);
            $table->integer('posicion_y')->default(0);
            $table->integer('ancho')->default(100);
            $table->integer('alto')->default(100);
            $table->integer('orden')->default(0);
            $table->json('configuracion')->nullable(); // JSON para props extras
            $table->timestamps();

            $table->foreign('layout_id')->references('id')->on('layouts')->onDelete('cascade');
            $table->foreign('caracteristica_id')->references('id')->on('caracteristicas')->onDelete('set null');
        });
    }

    public function down(): void {
        Schema::dropIfExists('layout_elementos');
    }
};
