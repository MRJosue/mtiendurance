<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orden_corte', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('orden_produccion_id');
            $table->foreign('orden_produccion_id')->references('id')->on('ordenes_produccion')->onDelete('cascade');

            $table->json('tallas');
            $table->json('tallas_entregadas')->nullable();
            $table->decimal('total', 10, 2)->default(0);
            $table->json('caracteristicas')->nullable();
            $table->date('fecha_inicio')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orden_corte');
    }
};
