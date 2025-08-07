<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario_staf_ordenes_produccion', function (Blueprint $table) {
            $table->id();

            $table->foreignId('orden_produccion_id')
                  ->constrained('ordenes_produccion')
                  ->onDelete('cascade');

            $table->foreignId('create_user')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->foreignId('assigned_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            $table->integer('cantidad_entregada')->default(0);
            $table->integer('cantidad_desperdicio')->default(0);
            $table->integer('total_entregado')->default(0);

            $table->tinyInteger('flag_activo')->default(0)->comment('0 = Inactivo, 1 = Activo');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_staf_ordenes_produccion');
    }
};
