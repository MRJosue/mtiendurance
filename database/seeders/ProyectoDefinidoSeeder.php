<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Proyecto;
use Illuminate\Support\Str;

class ProyectoDefinidoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
              // Inserta un proyecto definido
              $proyecto = Proyecto::create([
                'id' => Str::uuid(), // Genera un UUID
                'usuario_id' => 1, // Asigna un usuario existente (ajustar si es necesario)
                'nombre' => 'Proyecto Principal',
                'descripcion' => 'Este es un proyecto creado manualmente para pruebas.',
                'estado' => 'PENDIENTE',
                'fecha_creacion' => now(),
                'fecha_entrega' => now()->addDays(10),
            ]);

            // Muestra el ID del proyecto creado
            $this->command->info('Proyecto creado con ID: ' . $proyecto->id);

            // Usa el proyecto creado para generar pedidos
            \App\Models\Pedido::factory()->count(5)->create([
                'proyecto_id' => $proyecto->id, // Asigna el ID del proyecto creado
            ]);

            $this->command->info('Pedidos creados asociados al proyecto.');
    }
}
