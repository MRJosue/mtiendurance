<?php

namespace Database\Seeders;
use App\Models\Pedido;
use App\Models\Proyecto;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PedidosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    // Crea un proyecto válido
    $proyecto = Proyecto::factory()->create();
    $this->command->info('Proyecto creado con ID: ' . $proyecto->id);
    // Crea pedidos asociados al proyecto creado
    Pedido::factory()->count(5)->create([
        'proyecto_id' => $proyecto->id, // Asigna el proyecto creado
    ]);
    }
}
