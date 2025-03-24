<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            // Eliminar la relación con clientes
            $table->dropForeign(['usuario_id']);

            // Modificar la relación de usuario_id para que apunte a la tabla users
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_proyectos', function (Blueprint $table) {
            // Solo eliminar la FK si realmente existe
            if (Schema::hasColumn('pre_proyectos', 'usuario_id')) {
                try {
                    DB::statement("ALTER TABLE pre_proyectos DROP FOREIGN KEY pre_proyectos_usuario_id_foreign;");
                } catch (\Exception $e) {
                    // Si la FK no existe, ignorar el error
                }
            }
    
            // Restaurar la relación con clientes
            $table->foreign('usuario_id')->references('id')->on('clientes')->onDelete('cascade');
        });
    }
};
