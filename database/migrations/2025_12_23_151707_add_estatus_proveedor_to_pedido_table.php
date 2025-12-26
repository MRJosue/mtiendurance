<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->enum('estatus_proveedor', [
                'PENDIENTE',
                'VISTO',
                'EN_PROCESO',
                'LISTO',
            ])
            ->default('PENDIENTE')
            ->after('estado_produccion')
            ->comment('Estatus simple manejado por el proveedor');

            $table->timestamp('proveedor_visto_at')
                ->nullable()
                ->after('estatus_proveedor')
                ->comment('Fecha en que el proveedor revisó el pedido');

            $table->foreignId('proveedor_visto_por')
                ->nullable()
                ->after('proveedor_visto_at')
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Usuario proveedor que marcó el pedido como visto');

            $table->text('nota_proveedor')
                ->nullable()
                ->after('proveedor_visto_por')
                ->comment('Notas internas del proveedor sobre el pedido');
        });
    }

    public function down(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->dropForeign(['proveedor_visto_por']);
            $table->dropColumn([
                'estatus_proveedor',
                'proveedor_visto_at',
                'proveedor_visto_por',
                'nota_proveedor',
            ]);
        });
    }
};
