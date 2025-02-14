<?php

namespace Database\Seeders;
use App\Models\Opcion;
use App\Models\Caracteristica;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OpcionesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    // public function run(): void
    // {
    //     Opcion::factory(15)->create();
    // }

        /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $opciones = [
            'Bandola Económica',
            'Bandola Fundición',
            'Argolla de Llavero',
            'Bandola Caimán',
            'Algodon',
            'Lino',
            '20 mm',
            '30 mm',
            'Impresion a una cara',
            'Impresion a dos caras',
        ];


        
        foreach ($opciones as $nombreOpcion) {
                Opcion::create([
                    'nombre' => $nombreOpcion,
                    'valoru' => 1, // Puedes cambiar esto si necesitas un valor específico
                    'pasos' => 1,
                    'minutoPaso' => 1
                ]);
        }
        
    }
}
