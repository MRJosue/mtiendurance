<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('pedido', function (Blueprint $t) {
            $t->index('producto_id');
            $t->index('proyecto_id');
            $t->index('estado_id');
            $t->index('user_id');
            $t->index('total');
            $t->index('fecha_produccion');
            $t->index('fecha_embarque');
            $t->index('fecha_entrega');
        });

        Schema::table('productos', function (Blueprint $t) {
            $t->index('nombre');
            // $t->fullText('nombre'); // si vas por FULLTEXT
        });

        Schema::table('users', function (Blueprint $t) {
            $t->index('name');
            // $t->fullText('name'); // si usas FULLTEXT
        });

        Schema::table('proyectos', function (Blueprint $t) {
            $t->index('estado');
            $t->index('nombre');
            // $t->fullText(['nombre','estado']); // opcional
        });

        Schema::table('opciones', function (Blueprint $t) {
            $t->index('nombre');
            // $t->fullText('nombre'); // opcional
        });

        Schema::table('pedido_opciones', function (Blueprint $t) {
            $t->index(['opcion_id', 'pedido_id']);
            $t->index('pedido_id');
        });

        Schema::table('caracteristica_opcion', function (Blueprint $t) {
            $t->index(['caracteristica_id', 'opcion_id']);
        });
    }
};
