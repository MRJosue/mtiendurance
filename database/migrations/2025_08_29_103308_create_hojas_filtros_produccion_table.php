<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('hojas_filtros_produccion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->text('descripcion')->nullable();
            $table->unsignedBigInteger('role_id')->nullable(); // spatie roles.id (bigint)
            $table->json('estados_permitidos')->nullable();    // array de strings de pedidos.estado
            $table->json('base_columnas')->nullable();         // config columnas base
            $table->boolean('visible')->default(true);
            $table->integer('orden')->nullable();
            $table->timestamps();

            // si tu tabla roles es otra (spatie usa 'roles')
            $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::dropIfExists('hojas_filtros_produccion');
    }
};