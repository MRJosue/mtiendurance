<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pre_proyectos', function (Blueprint $table) {
            $table->boolean('flag_requiere_proveedor')
                ->default(false)
                ->comment('1 = Requiere proveedor, 0 = No requiere')
                ->after('flag_armado');
        });
    }

    public function down(): void
    {
        Schema::table('pre_proyectos', function (Blueprint $table) {
            $table->dropColumn('flag_requiere_proveedor');
        });
    }
};
