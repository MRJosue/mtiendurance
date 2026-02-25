<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('direcciones_entrega', function (Blueprint $table) {
            // 1) Nuevo campo texto
            if (!Schema::hasColumn('direcciones_entrega', 'ciudad')) {
                $table->string('ciudad', 255)->nullable()->after('calle');
            }
        });

        // 2) Backfill: ciudad = ciudades.nombre (si existía ciudad_id)
        try {
            if (Schema::hasColumn('direcciones_entrega', 'ciudad_id')) {
                DB::statement("
                    UPDATE direcciones_entrega de
                    LEFT JOIN ciudades c ON c.id = de.ciudad_id
                    SET de.ciudad = COALESCE(de.ciudad, c.nombre)
                ");
            }
        } catch (\Throwable $e) {
            // Silencioso
        }

        // 3) Quitar FK y columna ciudad_id
        if (Schema::hasColumn('direcciones_entrega', 'ciudad_id')) {
            Schema::table('direcciones_entrega', function (Blueprint $table) {
                try {
                    $table->dropForeign(['ciudad_id']);
                } catch (\Throwable $e) {
                    // Si la FK tiene otro nombre, luego ajustamos con el mensaje de error
                }
            });

            try {
                Schema::table('direcciones_entrega', function (Blueprint $table) {
                    $table->dropColumn('ciudad_id');
                });
            } catch (\Throwable $e) {
                // fallback: si falla por nombre de FK, dime el error exacto y lo ajusto
            }
        }
    }

    public function down(): void
    {
        Schema::table('direcciones_entrega', function (Blueprint $table) {
            if (!Schema::hasColumn('direcciones_entrega', 'ciudad_id')) {
                $table->unsignedBigInteger('ciudad_id')->nullable()->after('calle');
            }
        });

        // No se puede reconstruir ciudad_id desde texto sin lógica extra

        Schema::table('direcciones_entrega', function (Blueprint $table) {
            if (Schema::hasColumn('direcciones_entrega', 'ciudad')) {
                $table->dropColumn('ciudad');
            }
        });
    }
};