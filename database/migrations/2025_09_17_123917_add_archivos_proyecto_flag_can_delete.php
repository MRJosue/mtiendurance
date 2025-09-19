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
            $table->boolean('flag_can_delete')
                ->default(false)
                ->after('usuario_id')
                ->comment('Indica si el archivo puede ser eliminado manualmente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('archivos_proyecto', function (Blueprint $table) {
            $table->dropColumn('flag_can_delete');
        });
    }
};
