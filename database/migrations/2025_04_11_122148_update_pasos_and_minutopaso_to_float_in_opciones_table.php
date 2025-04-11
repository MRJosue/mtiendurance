<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('opciones', function (Blueprint $table) {
            $table->float('pasos')->change();
            $table->float('minutoPaso')->change();
        });
    }

    public function down()
    {
        Schema::table('opciones', function (Blueprint $table) {
            $table->integer('pasos')->change();
            $table->integer('minutoPaso')->change();
        });
    }
};
