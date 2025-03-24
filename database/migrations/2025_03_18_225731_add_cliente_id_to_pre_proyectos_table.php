<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pre_proyectos', function (Blueprint $table) {
            // Eliminar la relación con clientes
            $table->dropForeign(['usuario_id']);

            // Modificar la relación de usuario_id para que apunte a la tabla users
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');

            // Agregar el campo cliente_id sin clave foránea
            $table->unsignedBigInteger('cliente_id')->nullable()->after('usuario_id')->comment('Referencia del cliente sin FK');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_proyectos', function (Blueprint $table) {
            // Eliminar la nueva relación con users
            $table->dropForeign(['usuario_id']);

            // Restaurar la relación con clientes
            $table->foreign('usuario_id')->references('id')->on('clientes')->onDelete('cascade');

            // Eliminar el campo cliente_id
            $table->dropColumn('cliente_id');
        });
    }
};