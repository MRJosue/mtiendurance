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
            $table->tinyInteger('ind_activo')
                ->default(1)
                ->comment('1 = Activo, 0 = Eliminado lógico')
                ->after('last_uploaded_file_id');

            $table->index('ind_activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->dropIndex(['ind_activo']);
            $table->dropColumn('ind_activo');
        });
    }
};
