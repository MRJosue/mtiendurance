<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('layout_elementos', function (Blueprint $table) {
            $table->string('letra', 5)->nullable()->after('caracteristica_id');
        });
    }

    public function down(): void {
    if (Schema::hasColumn('layout_elementos', 'letra')) {
        Schema::table('layout_elementos', function (Blueprint $table) {
            $table->dropColumn('letra');
        });
    }
    }
};