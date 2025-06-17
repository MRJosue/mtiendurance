<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->float('total_minutos')->nullable()->after('total')->comment('Guarda la suma total de tiempo tomado de las opciones');
            $table->integer('total_pasos')->nullable()->after('total_minutos')->comment('Guarda el total de Operaciones / pasos');
            $table->json('resumen_tiempos')->nullable()->after('total_pasos')->comment('Resumen JSON con detalle de pasos, tiempos y opciones');
        });
    }

    public function down(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            $columns = Schema::getColumnListing('pedido');

            if (in_array('total_minutos', $columns)) {
                $table->dropColumn('total_minutos');
            }

            if (in_array('total_pasos', $columns)) {
                $table->dropColumn('total_pasos');
            }

            if (in_array('resumen_tiempos', $columns)) {
                $table->dropColumn('resumen_tiempos');
            }
        });
    }
};
