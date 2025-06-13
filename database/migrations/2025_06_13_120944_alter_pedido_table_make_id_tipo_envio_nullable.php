<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            // Si planeas dejarlo siempre nullable,
            // puedes dejar este método vacío o restaurar el NOT NULL:
            $table->unsignedBigInteger('id_tipo_envio')
                  ->nullable(false)
                  ->change();
        });
    }
};
