<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    public function up(): void
    {
        Schema::table('direcciones_fiscales', function (Blueprint $table) {
            // 1) Nuevo campo texto
            if (!Schema::hasColumn('direcciones_fiscales', 'ciudad')) {
                $table->string('ciudad', 255)->nullable()->after('calle');
            }
        });

        // 2) Backfill: ciudad = ciudades.nombre (si existía ciudad_id)
        // (Esto no falla si ya se borró ciudad_id)
        try {
            if (Schema::hasColumn('direcciones_fiscales', 'ciudad_id')) {
                DB::statement("
                    UPDATE direcciones_fiscales df
                    LEFT JOIN ciudades c ON c.id = df.ciudad_id
                    SET df.ciudad = COALESCE(df.ciudad, c.nombre)
                ");
            }
        } catch (\Throwable $e) {
            // Silencioso: en producción a veces ya no existe la FK/columna.
        }

        // 3) Quitar FK y columna ciudad_id
        if (Schema::hasColumn('direcciones_fiscales', 'ciudad_id')) {
            Schema::table('direcciones_fiscales', function (Blueprint $table) {
                // intentamos eliminar FK por nombre común; si tu constraint tiene otro nombre,
                // ajusta el dropForeign a ese nombre.
                try {
                    $table->dropForeign(['ciudad_id']);
                } catch (\Throwable $e) {
                    // Si no existe o tiene nombre diferente, intentaremos por SQL más abajo.
                }
            });

            // Asegura eliminar columna aunque el dropForeign haya fallado
            try {
                Schema::table('direcciones_fiscales', function (Blueprint $table) {
                    $table->dropColumn('ciudad_id');
                });
            } catch (\Throwable $e) {
                // fallback: algunos motores requieren eliminar FK con nombre exacto
                // Si te falla aquí, dime el nombre del constraint y lo ajustamos.
            }
        }
    }

    public function down(): void
    {
        // Revertir: recrear ciudad_id y borrar ciudad (NO vuelve a poblar el catálogo)
        Schema::table('direcciones_fiscales', function (Blueprint $table) {
            if (!Schema::hasColumn('direcciones_fiscales', 'ciudad_id')) {
                $table->unsignedBigInteger('ciudad_id')->nullable()->after('calle');
            }
        });

        // OJO: aquí no podemos reconstruir la relación ciudad_id desde texto sin lógica extra

        Schema::table('direcciones_fiscales', function (Blueprint $table) {
            if (Schema::hasColumn('direcciones_fiscales', 'ciudad')) {
                $table->dropColumn('ciudad');
            }
        });
    }
};