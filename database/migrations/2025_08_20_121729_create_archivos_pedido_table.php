<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archivos_pedido', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pedido_id')
                  ->constrained('pedido')
                  ->onDelete('cascade');

            $table->string('nombre_archivo');
            $table->string('ruta_archivo');
            $table->string('tipo_archivo')->nullable(); // mime type
            $table->tinyInteger('tipo_carga')->default(1)->comment('1=general, 2=otro, 3=evidencia entrega');
            $table->boolean('flag_descarga')->default(true);

            $table->foreignId('usuario_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->string('descripcion')->nullable();
            $table->unsignedInteger('version')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archivos_pedido');
    }
};
