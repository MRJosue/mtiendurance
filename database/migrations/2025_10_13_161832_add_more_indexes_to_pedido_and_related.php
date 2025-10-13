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
        // Índices compuestos útiles para filtros/orden
        Schema::table('pedido', function (Blueprint $t) {
            // estado_id + fecha_produccion  -> listas por estado y rango de fecha
            $t->index(['estado_id', 'fecha_produccion'], 'idx_pedido_estado_fecha_prod');

            // producto_id + estado_id       -> filtros combinados por producto/estado
            $t->index(['producto_id', 'estado_id'], 'idx_pedido_prod_estado');
        });

        // FULLTEXT para búsqueda global (si tienes la columna `pedido_busqueda`)
        if (Schema::hasColumn('pedido', 'pedido_busqueda')) {
            Schema::table('pedido', function (Blueprint $t) {
                // Nota: requiere MySQL/MariaDB con soporte FULLTEXT en tu motor (InnoDB OK en MySQL >=5.6)
                $t->fullText('pedido_busqueda', 'ft_pedido_busqueda');
            });
        }

        // Índice para catálogo de estados (ordenar/filtrar por nombre)
        if (Schema::hasTable('estados_pedido')) {
            Schema::table('estados_pedido', function (Blueprint $t) {
                $t->index('nombre', 'idx_estados_pedido_nombre');
            });
        }
    }

    public function down(): void
    {
        // Borra sólo lo que esta migración creó (por nombre)
        Schema::table('pedido', function (Blueprint $t) {
            $t->dropIndex('idx_pedido_estado_fecha_prod');
            $t->dropIndex('idx_pedido_prod_estado');

            // FULLTEXT
            // En Laravel se usa dropFullText si el índice es FULLTEXT
            if (Schema::hasColumn('pedido', 'pedido_busqueda')) {
                $t->dropFullText('ft_pedido_busqueda');
            }
        });

        if (Schema::hasTable('estados_pedido')) {
            Schema::table('estados_pedido', function (Blueprint $t) {
                $t->dropIndex('idx_estados_pedido_nombre');
            });
        }
    }
};
