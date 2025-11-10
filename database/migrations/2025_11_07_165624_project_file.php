<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_file', function (Blueprint $table) {
            $table->id('project_file_id');

            $table->foreignId('project_id')
                ->constrained('proyectos')
                ->cascadeOnDelete();

            $table->text('description')->nullable();
            $table->string('name')->nullable();

            // Mejor como boolean/tinyint
            $table->boolean('visibility_client')
                ->default(true)
                ->comment('1 visible, 0 hidden');

            $table->boolean('visibility_staff')
                ->default(true)
                ->comment('1 visible, 0 hidden');

            // Tamaño del archivo (bytes)
            $table->unsignedBigInteger('size')->default(0);

            // Si "type" es categoría numérica:
            $table->unsignedTinyInteger('type')->default(1);
            // -- O si es MIME, usa en cambio:
            // $table->string('type', 191)->nullable()->default('application/octet-stream');

            $table->unsignedInteger('type_id')->default(0);

            // Momento de subida
            $table->timestamp('timestamp_upload')->useCurrent();

            $table->timestamps();

            // Índices útiles
            $table->index('project_id');
            $table->index(['type', 'type_id']);
            $table->index('timestamp_upload');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_files');
    }
};
