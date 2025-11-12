<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sucursales', function (Blueprint $table) {
            // 1 = Principal, 2 = Secundaria
            $table->unsignedTinyInteger('tipo')
                  ->default(2)
                  ->comment('1=Principal, 2=Secundaria')
                  ->after('empresa_id');
        });
    }

    public function down(): void
    {
        Schema::table('sucursales', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};