<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductosTableSeeder extends Seeder
{

        /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = Categoria::all();

        foreach ($categorias as $categoria) {
            Producto::create([
                'nombre' => 'Producto 1 de ' . $categoria->nombre,
                'categoria_id' => '1',
              
            ]);

            Producto::create([
                'nombre' => 'Producto 2 de ' . $categoria->nombre,
                'categoria_id' => '2',
              
            ]);
        }
    }

    /**
     * Run the database seeds.
     */
    // public function run(): void
    // {
    //     Producto::factory(20)->create();
    // }
}
