<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opciones', function (Blueprint $table) {
            $table->time('minutoPaso')->change();
        });
    }

    public function down(): void
    {
        Schema::table('opciones', function (Blueprint $table) {
            $table->integer('minutoPaso')->change(); // Regresa a integer si haces rollback
        });
    }
};
