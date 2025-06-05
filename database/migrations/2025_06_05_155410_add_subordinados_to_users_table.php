<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Si usas MySQL 5.7+ o MariaDB 10.2.7+ puedes usar "json"
            $table->json('subordinados')->nullable()->after('user_can_sel_preproyectos');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('subordinados');
        });
    }
};
