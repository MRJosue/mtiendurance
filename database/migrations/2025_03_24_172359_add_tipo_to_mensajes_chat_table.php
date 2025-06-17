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
        Schema::table('mensajes_chat', function (Blueprint $table) {
            $table->tinyInteger('tipo')
                ->default(1)
                ->after('mensaje')
                ->comment('1 entrada de chat  2 evento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('mensajes_chat', 'tipo')) {
        Schema::table('mensajes_chat', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
        }
    }
};
