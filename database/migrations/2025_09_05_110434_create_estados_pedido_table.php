<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('estados_pedido', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();                 // Ej: "POR APROBAR"
            $table->string('slug')->unique();                   // Ej: "por-aprobar"
            $table->unsignedInteger('orden')->nullable();       // Para ordenar tabs/listas
            $table->string('color')->nullable();                // tailwind o hex, ej: "blue" / "#2563eb"
            $table->boolean('ind_activo')->default(true);
            $table->timestamps();
        });

        // Semilla mínima in-migration (si prefieres, pásalo a un seeder)
        $rows = [
            ['nombre' => 'POR APROBAR',          'slug' => 'por-aprobar',          'orden' => 10, 'color' => 'bg-yellow-400 text-black', 'ind_activo' => true],
            ['nombre' => 'POR PROGRAMAR',        'slug' => 'por-programar',        'orden' => 20, 'color' => 'bg-amber-400 text-black',  'ind_activo' => true],
            ['nombre' => 'APROBADO',             'slug' => 'aprobado',             'orden' => 30, 'color' => 'bg-green-600 text-white',  'ind_activo' => true],
            ['nombre' => 'EN PRODUCCION',        'slug' => 'en-produccion',        'orden' => 40, 'color' => 'bg-orange-500 text-white', 'ind_activo' => true],
            ['nombre' => 'LISTO PARA ENTREGAR',  'slug' => 'listo-para-entregar',  'orden' => 50, 'color' => 'bg-cyan-500 text-white',   'ind_activo' => true],
            ['nombre' => 'ENTREGADO',            'slug' => 'entregado',            'orden' => 60, 'color' => 'bg-emerald-600 text-white','ind_activo' => true],
            ['nombre' => 'RECHAZADO',            'slug' => 'rechazado',            'orden' => 70, 'color' => 'bg-red-600 text-white',    'ind_activo' => true],
            ['nombre' => 'ARCHIVADO',            'slug' => 'archivado',            'orden' => 80, 'color' => 'bg-gray-500 text-white',   'ind_activo' => true],
            ['nombre' => 'RECONFIGURAR',         'slug' => 'reconfigurar',         'orden' => 90, 'color' => 'bg-purple-600 text-white', 'ind_activo' => true],
        ];

        DB::table('estados_pedido')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('estados_pedido');
    }
};
