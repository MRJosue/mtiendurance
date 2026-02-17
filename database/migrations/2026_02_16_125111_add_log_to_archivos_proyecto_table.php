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
        Schema::table('archivos_proyecto', function (Blueprint $table) {
            $table->json('log')
                  ->nullable()
                  ->after('descripcion')
                  ->comment('Historial de eventos del archivo en formato JSON');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('archivos_proyecto', function (Blueprint $table) {
            $table->dropColumn('log');
        });
    }
};