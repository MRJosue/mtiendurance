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
        Schema::table('caracteristicas', function (Blueprint $table) {
            //
            $table->tinyInteger('ind_activo')->default(1)->comment('Define si el registro esta activo 1 = activo 0 = in activo');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

         if (Schema::hasColumn('caracteristicas', 'ind_activo')) {
            Schema::table('caracteristicas', function (Blueprint $table) {
                //
                $table->dropColumn('ind_activo');
            });
         }
    }
};
