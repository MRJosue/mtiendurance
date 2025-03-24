<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    // /**
    //  * Run the migrations.
    //  */
    // public function up(): void
    // {
    //     Schema::table('pedido', function (Blueprint $table) {
    //         // Eliminar la clave foránea de cliente_id si existe
    //         $table->dropForeign(['cliente_id']);
        
    //         // Modificar cliente_id para que sea solo un campo de referencia sin clave foránea
    //         $table->unsignedBigInteger('cliente_id')->nullable()->default(null)->change();
        
    //         // Solo agrega la columna user_id si no existe
    //         if (!Schema::hasColumn('pedido', 'user_id')) {
    //             $table->unsignedBigInteger('user_id');
    //         }
        
    //         // También asegúrate de no duplicar la foreign key
    //         $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
    //             WHERE TABLE_NAME = 'pedido' AND COLUMN_NAME = 'user_id' AND CONSTRAINT_SCHEMA = DATABASE();");
        
    //         if (empty($foreignKeys)) {
    //             $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    //         }
    //     });
    // }

    // /**
    //  * Reverse the migrations.
    //  */
    // public function down(): void
    // {
    //     Schema::table('pedido', function (Blueprint $table) {
    //         // Asegurar que no haya cliente_id nulos
    //         DB::table('pedido')->whereNull('cliente_id')->update(['cliente_id' => 1]);
    
    //         // Restaurar la restricción NOT NULL
    //         $table->unsignedBigInteger('cliente_id')->nullable(false)->change();
    
    //         // Restaurar la relación
    //         $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
    
    //         // Eliminar user_id
    //         $table->dropForeign(['user_id']);
    //         $table->dropColumn('user_id');
    //     });
    // }
};
