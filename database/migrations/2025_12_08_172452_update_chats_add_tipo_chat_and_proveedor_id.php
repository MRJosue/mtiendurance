<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            // Nuevo campo: tipo de chat
            // 1 = CLIENTE, 2 = PROVEEDOR
            $table->tinyInteger('tipo_chat')
                  ->default(1)
                  ->comment('1 = cliente, 2 = proveedor')
                  ->after('proyecto_id');

            // Nuevo campo: "proveedor" relacionado (en realidad es cualquier USER)
            $table->unsignedBigInteger('proveedor_id')
                  ->nullable()
                  ->after('tipo_chat');

            // FK: proveedor_id -> users.id (no a proveedores)
            $table->foreign('proveedor_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();

            // Índice compuesto útil para búsquedas
            $table->index(['proyecto_id', 'tipo_chat', 'proveedor_id'], 'idx_chat_contexto');
        });
    }

    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            // Eliminar índice
            $table->dropIndex('idx_chat_contexto');

            // Eliminar FK (Laravel la nombra chats_proveedor_id_foreign)
            $table->dropForeign(['proveedor_id']);

            // Eliminar columnas nuevas
            $table->dropColumn(['tipo_chat', 'proveedor_id']);
        });
    }
};
