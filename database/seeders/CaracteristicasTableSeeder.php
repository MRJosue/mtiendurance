<?php

namespace Database\Seeders;
use App\Models\Caracteristica;
use App\Models\Producto;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CaracteristicasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    // public function run(): void
    // {
    //     Caracteristica::factory(10)->create();
    // }

        /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $caracteristicas = [
            'Material',
            'Medida',
            'Impresión',
            'Herraje',
        ];

            foreach ($caracteristicas as $nombreCaracteristica) {
                Caracteristica::create([
                    'nombre' => $nombreCaracteristica
                ]);
            }
        
    }
}
