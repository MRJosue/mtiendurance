<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    public function up(): void
    {
        // 1) Tabla de tipos de permiso
        Schema::create('permission_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();          // p.ej: 'sistema', 'modulo', 'accion'
            $table->string('nombre');                  // nombre legible: 'Sistema', 'Módulo', 'Acción'
            $table->unsignedInteger('orden')->nullable();
            $table->timestamps();
        });

        // 2) Insertar 3 tipos base (puedes renombrarlos si gustas)
        DB::table('permission_types')->insert([
            ['slug' => 'sistema', 'nombre' => 'Sistema', 'orden' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'modulo',  'nombre' => 'Módulo',  'orden' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'accion',  'nombre' => 'Acción',  'orden' => 3, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 3) Alterar permissions: agregar nombre, orden y FK a permission_types
        Schema::table(config('permission.table_names.permissions'), function (Blueprint $table) {
            // Campo "nombre" que se mostrará del lado del usuario
            $table->string('nombre')->nullable()->after('name');

            // Campo "orden" para ordenar visualmente (nullable)
            $table->unsignedInteger('orden')->nullable()->after('nombre');

            // Relación opcional al tipo de permiso (nullable)
            $table->foreignId('permission_type_id')
                ->nullable()
                ->after('orden')
                ->constrained('permission_types')
                ->nullOnDelete();
        });

        // 4) Rellenar "nombre" con el valor actual de "name" para registros existentes
        DB::table(config('permission.table_names.permissions'))
            ->whereNull('nombre')
            ->update(['nombre' => DB::raw('name')]);
    }

    public function down(): void
    {
        // Quitar FK/columnas en permissions
        Schema::table(config('permission.table_names.permissions'), function (Blueprint $table) {
            if (Schema::hasColumn(config('permission.table_names.permissions'), 'permission_type_id')) {
                $table->dropConstrainedForeignId('permission_type_id');
            }

            if (Schema::hasColumn(config('permission.table_names.permissions'), 'orden')) {
                $table->dropColumn('orden');
            }

            if (Schema::hasColumn(config('permission.table_names.permissions'), 'nombre')) {
                $table->dropColumn('nombre');
            }
        });

        // Dropear tabla de tipos
        Schema::dropIfExists('permission_types');
    }
};
