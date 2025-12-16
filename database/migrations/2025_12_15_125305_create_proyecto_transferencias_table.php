<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
    {
        Schema::create('proyecto_transferencias', function (Blueprint $table) {
            $table->id();

            // Relaciones principales
            $table->foreignId('proyecto_id')
                ->constrained('proyectos')
                ->cascadeOnDelete();

            $table->foreignId('owner_actual_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('owner_nuevo_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Flujo del proceso
            $table->foreignId('solicitado_por_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('aprobado_por_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('estado', [
                'PENDIENTE',
                'APROBADO',
                'RECHAZADO',
                'APLICADO',
                'CANCELADO'
            ])->default('PENDIENTE');

            // Metadata
            $table->text('motivo')->nullable();

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('applied_at')->nullable();

            $table->timestamps();

            // Índices útiles
            $table->index(['proyecto_id', 'estado'], 'idx_transferencias_proyecto_estado');
            $table->index('owner_nuevo_id', 'idx_transferencias_owner_nuevo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proyecto_transferencias');
    }
};
