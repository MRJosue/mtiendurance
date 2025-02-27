<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Categoria::factory(2)->create();
        Categoria::create(['nombre' => 'Listones']);
        Categoria::create(['nombre' => 'Playeras','flag_tallas' => 1]);
        Categoria::create(['nombre' => 'Medalla']);
        Categoria::create(['nombre' => 'Pulsera']);
        Categoria::create(['nombre' => 'Gorras']);
        Categoria::create(['nombre' => 'Portagafetes']);
 

    }
}
