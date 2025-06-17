<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Agrega el campo JSON
        Schema::table('users', function (Blueprint $table) {
            $table->json('config')->nullable()->after('remember_token')->comment('Configuraciones personalizadas como flags');
        });

        // Establece los valores iniciales para usuarios existentes
        DB::table('users')->update([
            'config' => json_encode([
                'flag-user-sel-preproyectos' => true,
                'flag-can-user-sel-preproyectos' => false,
            ])
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'user_can_sel_preproyectos')) {
                $table->dropColumn('user_can_sel_preproyectos');
            }
        });
    }
};