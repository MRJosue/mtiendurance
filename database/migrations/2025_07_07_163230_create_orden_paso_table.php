<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('orden_paso', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('orden_produccion_id')
                  ->constrained('ordenes_produccion')
                  ->onDelete('cascade');
            $table->string('nombre', 100);
            $table->unsignedInteger('grupo_paralelo');
            $table->enum('estado', ['PENDIENTE','EN_PROCESO','COMPLETADO'])
                  ->default('PENDIENTE');
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_fin')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orden_paso');
    }
};
