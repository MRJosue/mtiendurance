<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
    public function up()
    {
        Schema::create('opciones', function (Blueprint $table) {
            // $table->id ();
            // $table->unsignedBigInteger('caracteristica_id');
            // $table->string('nombre');
            // $table->string('valor');
            // $table->foreign('caracteristica_id')->references('id')->on('caracteristicas')->onDelete('cascade');
            // $table->timestamps();
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
        Schema::dropIfExists('opciones');
    }
};
