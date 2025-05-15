<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGruposOrdenTable extends Migration
{
    public function up()
    {
        Schema::create('grupos_orden', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('grupos_orden');
    }
}
