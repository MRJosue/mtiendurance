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
        Schema::table('grupos_orden', function (Blueprint $table) {
            if (!Schema::hasColumn('grupos_orden', 'slug')) {
                $table->string('slug')->after('nombre');
                $table->unique('slug');
            }
            if (!Schema::hasColumn('grupos_orden', 'orden')) {
                $table->unsignedInteger('orden')->default(0)->after('slug');
                $table->index('orden');
            }
        });
    }

    public function down(): void
    {
        Schema::table('grupos_orden', function (Blueprint $table) {
            if (Schema::hasColumn('grupos_orden', 'orden')) {
                $table->dropIndex(['orden']);
                $table->dropColumn('orden');
            }
            if (Schema::hasColumn('grupos_orden', 'slug')) {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            }
        });
    }
};
