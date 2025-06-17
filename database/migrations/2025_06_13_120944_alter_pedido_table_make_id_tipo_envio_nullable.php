<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Hacer que id_tipo_envio sea nullable.
     */
    public function up(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            // Necesitas doctrine/dbal para usar change() en MySQL:
            // composer require doctrine/dbal
            $table->unsignedBigInteger('id_tipo_envio')
                  ->nullable()
                  ->change();
        });
    }

    /**
     * Revertir el cambio (volver a NOT NULL).
     */
    public function down(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
                // Asigna un valor por defecto a las filas con NULL
                DB::statement("UPDATE pedido SET id_tipo_envio = 1 WHERE id_tipo_envio IS NULL");

                Schema::table('pedido', function (Blueprint $table) {
                    $table->unsignedBigInteger('id_tipo_envio')
                        ->nullable(false)
                        ->change();
                });
        });
    }
};
