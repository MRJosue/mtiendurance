<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
    public function up()
    {
        Schema::create('caracteristicas', function (Blueprint $table) {

            $table->id();
            $table->string('nombre');
            $table->tinyInteger('flag_seleccion_multiple')->default(1)->comment('Flag seleccion multiple');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('caracteristicas');
    }
};
