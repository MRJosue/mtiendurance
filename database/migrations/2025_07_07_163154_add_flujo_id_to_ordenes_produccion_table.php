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
        Schema::table('ordenes_produccion', function (Blueprint $table) {
            // Solo campo numérico, puede ser null
            $table->integer('flujo_id')->nullable()->after('id');
        });
    }

    public function down()
    {
        Schema::table('ordenes_produccion', function (Blueprint $table) {
            $table->dropColumn('flujo_id');
        });
    }
};
