<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('direcciones_fiscales', function (Blueprint $table) {
            // La dejamos nullable para no romper registros existentes; el componente ya la exige.
            $table->string('razon_social', 255)->nullable()->after('usuario_id');
        });

        // (Opcional) Backfill rápido: copia RFC si falta razon_social
        // DB::table('direcciones_fiscales')
        //   ->whereNull('razon_social')
        //   ->update(['razon_social' => DB::raw('rfc')]);

        // Si quisieras volverla NOT NULL necesitarías doctrine/dbal y luego:
        // Schema::table('direcciones_fiscales', function (Blueprint $table) {
        //     $table->string('razon_social', 255)->nullable(false)->change();
        // });
    }

    public function down(): void
    {
        Schema::table('direcciones_fiscales', function (Blueprint $table) {
            $table->dropColumn('razon_social');
        });
    }
};
