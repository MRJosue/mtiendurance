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
        Schema::table('pedido', function (Blueprint $table) {
            // Agregar columna de URL 
            $table->string('url')->nullable();

            // Agregar referencia al último archivo cargado
            $table->unsignedBigInteger('last_uploaded_file_id')->nullable()
                  ->comment('Referencia al último archivo cargado en el proyecto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
     if (Schema::hasColumn('pedido', 'url')) {
        Schema::table('pedido', function (Blueprint $table) {
            $table->dropColumn(['url', 'last_uploaded_file_id']);
        });
      }
    }
};
