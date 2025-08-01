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
        Schema::table('direcciones_entrega', function (Blueprint $table) {
            $table->string('nombre_empresa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('direcciones_entrega', 'nombre_empresa')) {
            Schema::table('direcciones_entrega', function (Blueprint $table) {
                $table->dropColumn('nombre_empresa');
            });
        }
    }
};
