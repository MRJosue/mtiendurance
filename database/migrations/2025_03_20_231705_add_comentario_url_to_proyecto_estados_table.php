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
        Schema::table('proyecto_estados', function (Blueprint $table) {
            // Agregar columna de comentario (texto largo)
            $table->text('comentario')->nullable()->after('estado');

            // Agregar columna de URL (cadena de texto)
            $table->string('url')->nullable()->after('comentario');

            // Agregar referencia no estricta al último archivo cargado
            $table->unsignedBigInteger('last_uploaded_file_id')->nullable()->after('url')
                ->comment('Referencia al último archivo cargado en el proyecto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proyecto_estados', function (Blueprint $table) {
            // Eliminar las columnas agregadas si se revierte la migración
            $table->dropColumn('comentario');
            $table->dropColumn('url');
            $table->dropColumn('last_uploaded_file_id');

            
        });
    }
};
