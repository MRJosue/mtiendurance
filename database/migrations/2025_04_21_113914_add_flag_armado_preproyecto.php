<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pre_proyectos', function (Blueprint $table) {
            $table->tinyInteger('flag_armado')
                  ->default(1)
                  ->after('categoria_sel')
                  ->comment('1 = Activo (Los pedidos seran armados), 0 = Inactivo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('pre_proyectos', 'flag_armado')) {
            Schema::table('pre_proyectos', function (Blueprint $table) {
                $table->dropColumn('flag_armado');
            });
        }
    }
};
