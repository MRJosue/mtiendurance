<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pre_proyecto_aprobaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pre_proyecto_id');
            $table->foreignId('proyecto_id')
                ->nullable()
                ->constrained('proyectos')
                ->nullOnDelete();
            $table->foreignId('aprobado_por_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->enum('estado', ['PROCESSING', 'COMPLETED', 'FAILED'])->default('PROCESSING');
            $table->text('error')->nullable();
            $table->timestamp('aprobado_at')->nullable();
            $table->timestamps();

            $table->unique('pre_proyecto_id', 'uq_pre_proyecto_aprobaciones_pre_proyecto');
            $table->index(['estado', 'created_at'], 'idx_pre_proyecto_aprobaciones_estado_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_proyecto_aprobaciones');
    }
};
