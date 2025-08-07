<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordenes_produccion', function (Blueprint $table) {
            $table->unsignedTinyInteger('prioridad')->default(3)->comment('1: Alta, 2: Media, 3: Baja')->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('ordenes_produccion', function (Blueprint $table) {
            $table->dropColumn('prioridad');
        });
    }
};
