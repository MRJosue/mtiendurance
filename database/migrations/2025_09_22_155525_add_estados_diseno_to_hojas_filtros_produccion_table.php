<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    
    public function up(): void
    {
        Schema::table('hojas_filtros_produccion', function (Blueprint $table) {
            // Lista de ESTADOS DE PROYECTO (diseño) seleccionados para el viewer
            // Se guarda como arreglo de strings, p. ej. ["PENDIENTE","DISEÑO APROBADO"]
            $table->json('estados_diseno_permitidos')
                ->nullable()
                ->after('estados_permitidos');
        });
    }

    public function down(): void
    {
        Schema::table('hojas_filtros_produccion', function (Blueprint $table) {
            $table->dropColumn('estados_diseno_permitidos');
        });
    }
};
