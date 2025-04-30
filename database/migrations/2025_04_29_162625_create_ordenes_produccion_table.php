<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordenes_produccion', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('crete_user');
            $table->foreign('crete_user')->references('id')->on('users')->onDelete('cascade');

            $table->enum('tipo', ['CORTE', 'BORDADO', 'PINTURA', 'ETIQUETADO', 'OTRO'])->default('CORTE');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordenes_produccion');
    }
};
