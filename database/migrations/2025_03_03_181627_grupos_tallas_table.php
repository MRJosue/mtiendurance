<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupos_tallas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique()->comment('Nombre del grupo de tallas, por ejemplo, Hombre, Mujer, Niños');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupos_tallas');
    }
};
