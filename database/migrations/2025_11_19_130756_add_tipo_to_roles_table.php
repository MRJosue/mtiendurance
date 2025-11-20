<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->unsignedTinyInteger('tipo')
                ->nullable()
                ->after('guard_name')
                ->comment('1=CLIENTE,2=PROVEEDOR,3=STAFF,4=ADMIN');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
