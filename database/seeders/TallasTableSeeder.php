<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Talla;
class TallasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tallas = [
            ['nombre' => 'XS', 'descripcion' => 'Extra pequeño, ideal para cuerpos muy delgados.'],
            ['nombre' => 'S', 'descripcion' => 'Pequeño, adecuado para cuerpos delgados.'],
            ['nombre' => 'M', 'descripcion' => 'Mediano, la talla estándar para la mayoría.'],
            ['nombre' => 'L', 'descripcion' => 'Grande, para quienes necesitan un poco más de espacio.'],
            ['nombre' => 'XL', 'descripcion' => 'Extra grande, diseñado para mayor comodidad.'],
            ['nombre' => 'XXL', 'descripcion' => 'Doble extra grande, para cuerpos más amplios.'],
        ];

        foreach ($tallas as $talla) {
            Talla::create($talla);
        }
    }
}

