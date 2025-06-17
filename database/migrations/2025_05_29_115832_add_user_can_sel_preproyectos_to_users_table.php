<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
           $table->json('user_can_sel_preproyectos')->nullable()->after('config');
        });
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
