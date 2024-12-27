<?php

namespace Database\Factories;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Producto>
 */
class ProductoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Producto::class;

    public function definition(): array
    {
        return [
            //'id' => $this->faker->uuid, // Genera un UUID para la clave primaria
            'nombre' => $this->faker->word, // Genera un nombre aleatorio
            'categoria_id' => Categoria::factory(), // Crea una categoría y asigna su ID
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
