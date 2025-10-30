<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega campos legacy que provienen de la tabla `client` del sistema anterior:
     * - client.user      -> users.user_legacy        (TEXT, nullable)
     * - client.company   -> users.company_legacy     (TEXT, nullable)
     * - client.super     -> users.super_legacy       (INT,  nullable, index)
     * - client.super_id  -> users.super_id_legacy    (INT,  nullable, index)
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // TEXT (LONGTEXT en legado), cercanos a credenciales/datos básicos
            if (!Schema::hasColumn('users', 'user_legacy')) {
                $table->text('user_legacy')->nullable()->after('password')
                    ->comment('Valor legacy de client.user');
            }
            if (!Schema::hasColumn('users', 'company_legacy')) {
                $table->text('company_legacy')->nullable()->after('user_legacy')
                    ->comment('Valor legacy de client.company');
            }

            // INT (en legado eran INT)
            if (!Schema::hasColumn('users', 'super_legacy')) {
                $table->integer('super_legacy')->nullable()->after('company_legacy')
                    ->comment('Valor legacy de client.super');
                $table->index('super_legacy');
            }
            if (!Schema::hasColumn('users', 'super_id_legacy')) {
                $table->integer('super_id_legacy')->nullable()->after('super_legacy')
                    ->comment('Valor legacy de client.super_id');
                $table->index('super_id_legacy');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar índices si tu motor los requiere explícitamente antes de soltar columnas
            if (Schema::hasColumn('users', 'super_legacy')) {
                $table->dropIndex(['super_legacy']);
            }
            if (Schema::hasColumn('users', 'super_id_legacy')) {
                $table->dropIndex(['super_id_legacy']);
            }

            // Drop de columnas
            if (Schema::hasColumn('users', 'super_id_legacy')) {
                $table->dropColumn('super_id_legacy');
            }
            if (Schema::hasColumn('users', 'super_legacy')) {
                $table->dropColumn('super_legacy');
            }
            if (Schema::hasColumn('users', 'company_legacy')) {
                $table->dropColumn('company_legacy');
            }
            if (Schema::hasColumn('users', 'user_legacy')) {
                $table->dropColumn('user_legacy');
            }
        });
    }
};
