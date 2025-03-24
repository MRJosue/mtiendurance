<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;



return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pedido_tallas', function (Blueprint $table) {
            // 🔴 Eliminar primero la clave primaria compuesta
            $table->dropForeign(['pedido_id']);
            $table->dropForeign(['talla_id']);
            $table->dropPrimary(['pedido_id', 'talla_id']); 
        });

        Schema::table('pedido_tallas', function (Blueprint $table) {
            // ✅ Agregar una columna `id` autoincrementable como nueva clave primaria
            $table->id()->first();

            // ✅ Restaurar las claves foráneas eliminadas
            $table->foreign('pedido_id')->references('id')->on('pedido')->onDelete('cascade');
            $table->foreign('talla_id')->references('id')->on('tallas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedido_tallas', function (Blueprint $table) {
            if (Schema::hasColumn('pedido_tallas', 'id')) {
                // 🔴 Solo eliminar la columna si existe
                $table->dropColumn('id');
            }
    
            // 🔴 Restaurar la clave primaria compuesta solo si no existe
            if (!Schema::hasColumn('pedido_tallas', 'pedido_id') || !Schema::hasColumn('pedido_tallas', 'talla_id')) {
                $table->primary(['pedido_id', 'talla_id']);
            }
        });
    }
};