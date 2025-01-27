<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
    public function up()
    {
        Schema::create('caracteristicas', function (Blueprint $table) {
            // $table->id();
            // $table->string('nombre');

            // $table->unsignedBigInteger('producto_id'); // Tipo compatible con `productos.id`
            // $table->foreign('producto_id')->references('id')->on('productos')->onDelete('cascade');

            // $table->uuid('producto_id');
            // $table->foreign('producto_id')->references('id')->on('productos')->onDelete('cascade');
            $table->id();
            $table->string('nombre');
            $table->integer('pasos');
            $table->integer('minutoPaso');
            $table->float('valoru');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('caracteristicas');
    }
};
