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
            // Añadir el flag_reconfigurar
            $table->boolean('flag_reconfigurar')
                ->default(0)
                ->after('total_piezas_sel')
                ->comment('1 = Reconfiguración activa, 0 = Inactiva');

                
           $table->boolean('flag_solicitud_reconfigurar')
                ->default(0)
                ->after('flag_reconfigurar')
                ->comment('1 = Solicitud activa, 0 = Inactiva');


            // Añadir el flag activo
            $table->boolean('activo')
                ->default(1)
                ->after('flag_solicitud_reconfigurar')
                ->comment('1 = Activo, 0 = Inactivo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->dropColumn(['flag_reconfigurar', 'activo']);
        });
    }
};
