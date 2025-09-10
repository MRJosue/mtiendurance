<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Agregar columna estado_id si no existe (nullable y default null)
        if (!Schema::hasColumn('pedido', 'estado_id')) {
            Schema::table('pedido', function (Blueprint $table) {
                $table->unsignedBigInteger('estado_id')
                      ->nullable()
                      ->default(null)   // 👈 por defecto null
                      ->after('estado');
            });
        }

        // 2) Poblar estado_id desde la columna estado (solo si hay match)
        DB::statement("
            UPDATE pedido p
            JOIN estados_pedido e ON e.nombre = p.estado
            SET p.estado_id = e.id
            WHERE p.estado IS NOT NULL
        ");

        // 3) Agregar FK (permitiendo null)
        Schema::table('pedido', function (Blueprint $table) {
            $table->foreign('estado_id', 'pedido_estado_id_foreign')
                  ->references('id')->on('estados_pedido')
                  ->cascadeOnUpdate()
                  ->nullOnDelete(); // 👈 si se borra el estado, queda en null
        });
    }

    public function down(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            if (Schema::hasColumn('pedido', 'estado_id')) {
                $table->dropForeign('pedido_estado_id_foreign');
                $table->dropColumn('estado_id');
            }
        });
    }
};
