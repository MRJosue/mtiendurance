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
        Schema::create('pedido_opciones', function (Blueprint $table) {


            $table->id();
            $table->unsignedBigInteger('pedido_id');
            $table->unsignedBigInteger('opcion_id');
            $table->string('valor')->nullable(); // Define la columna `valor`
            $table->timestamps();

            $table->foreign('pedido_id')->references('id')->on('pedido')->onDelete('cascade');
            $table->foreign('opcion_id')->references('id')->on('opciones')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_opciones');
    }
};
