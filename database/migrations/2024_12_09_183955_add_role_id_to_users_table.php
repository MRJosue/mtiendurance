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
        Schema::table('users', function (Blueprint $table) {
                        // Agregar la columna role_id
                        $table->unsignedBigInteger('role_id')->nullable();

                        // Establecer la relación con la tabla roles
                        $table->foreign('role_id')
                              ->references('id')->on('roles')
                              ->onDelete('set null'); // Si el rol se elimina, se pone en null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
                        // Eliminar la relación y la columna role_id
                        $table->dropForeign(['role_id']);
                        $table->dropColumn('role_id');

        });
    }
};
