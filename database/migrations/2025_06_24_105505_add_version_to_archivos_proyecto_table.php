<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('archivos_proyecto', function (Blueprint $table) {
            $table->unsignedInteger('version')->default(1)->after('flag_descarga');
        });
    }

    public function down()
    {
        Schema::table('archivos_proyecto', function (Blueprint $table) {
            $table->dropColumn('version');
        });
    }
};
