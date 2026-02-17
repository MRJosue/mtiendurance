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
        Schema::table('proyectos', function (Blueprint $table) {
            $table->tinyInteger('flag_reconfigurado')
                ->default(0)
                ->comment('1 = Proyecto ya reconfigurado, 0 = No reconfigurado')
                ->after('flag_reconfigurar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->dropColumn('flag_reconfigurado');
        });
    }
};
