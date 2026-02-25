<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('estado_tipo_envio', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('estado_id');
            $table->unsignedBigInteger('tipo_envio_id');
            $table->timestamps();

            $table->unique(['estado_id', 'tipo_envio_id'], 'uq_estado_tipo_envio');

            $table->foreign('estado_id')->references('id')->on('estados')->cascadeOnDelete();
            $table->foreign('tipo_envio_id')->references('id')->on('tipo_envio')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estado_tipo_envio');
    }
};