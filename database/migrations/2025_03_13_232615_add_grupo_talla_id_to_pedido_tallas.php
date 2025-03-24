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
        Schema::table('pedido_tallas', function (Blueprint $table) {
            $table->unsignedBigInteger('grupo_talla_id')->after('talla_id'); // Nueva FK después de talla_id
            $table->foreign('grupo_talla_id')->references('id')->on('grupos_tallas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedido_tallas', function (Blueprint $table) {
            if (Schema::hasColumn('pedido_tallas', 'grupo_talla_id')) {
                try {
                    $table->dropForeign(['grupo_talla_id']); // Elimina la FK solo si existe
                } catch (\Illuminate\Database\QueryException $e) {
                    // Si la FK no existe, ignorar el error
                }
                $table->dropColumn('grupo_talla_id');
            }
        });
    }
};