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
        Schema::table('pedido', function (Blueprint $table) {
            // Eliminar la clave foránea de cliente_id si existe
            $table->dropForeign(['cliente_id']);
            
            // Modificar cliente_id para que sea solo un campo de referencia sin clave foránea
            $table->unsignedBigInteger('cliente_id')->nullable()->default(null)->change();
            
            // Agregar el campo user_id relacionado con users
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            // Restaurar cliente_id como clave foránea
            $table->unsignedBigInteger('cliente_id')->nullable(false)->change();
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            
            // Eliminar user_id
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
