<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;


use Illuminate\Database\Seeder;
use App\Models\Tarea;
use App\Models\Proyecto;
use App\Models\User;
use Faker\Factory as Faker;

class TareasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Obtener todos los proyectos
        $proyectos = Proyecto::all();

        // Obtener todos los IDs de usuarios
        $usuariosIds = User::pluck('id')->toArray();

        foreach ($proyectos as $proyecto) {
            for ($i = 0; $i < 5; $i++) {
                Tarea::create([
                    'proyecto_id' => $proyecto->id,
                    'staff_id' => $faker->randomElement($usuariosIds),
                    'descripcion' => $faker->sentence(),
                    'estado' => $faker->randomElement(['PENDIENTE', 'EN PROCESO', 'COMPLETADA']),
                ]);
            }
        }
    }
}


// class TareasTableSeeder extends Seeder
// {
//     /**
//      * Run the database seeds.
//      */
//     public function run(): void
//     {
//         Proyecto::factory(400)->create();
//     }
// }
