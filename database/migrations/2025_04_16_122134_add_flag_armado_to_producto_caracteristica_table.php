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
        Schema::table('producto_caracteristica', function (Blueprint $table) {
            $table->tinyInteger('flag_armado')
                  ->default(0)
                  ->after('caracteristica_id')
                  ->comment('1 = Activo (mostrar en armado), 0 = Inactivo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        if (Schema::hasColumn('producto_caracteristica', 'flag_armado')) {
            Schema::table('producto_caracteristica', function (Blueprint $table) {
                $table->dropColumn('flag_armado');
            });
        }
    }
};
