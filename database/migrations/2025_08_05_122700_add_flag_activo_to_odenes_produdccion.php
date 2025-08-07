<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordenes_produccion', function (Blueprint $table) {
            $table->tinyInteger('flag_activo')
                  ->default(0)
                  ->comment('0 = Inactivo, 1 = Activo')
                  ->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('ordenes_produccion', function (Blueprint $table) {
            $table->dropColumn('flag_activo');
        });
    }
};