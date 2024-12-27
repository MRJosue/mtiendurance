<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Categoria;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Categoria>
 */
class CategoriaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Categoria::class;
    public function definition(): array
    {
        // 'id' => $this->faker->uuid, // Genera un UUID para la clave primaria
        return [

            'nombre' => $this->faker->word, // Genera un nombre aleatorio
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
