<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('productos', function (Blueprint $table) {

            $table->id();
            $table->string('nombre');
            $table->integer('dias_produccion')->default(6)->comment('Variable que se usara para calcular fechas en preproyectos');
            $table->tinyInteger('flag_armado')->default(1)->comment('Flag para validar si va armado');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('productos');
    }
};
