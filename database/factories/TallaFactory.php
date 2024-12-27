<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Talla;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Talla>
 */
class TallaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Talla::class;
    public function definition(): array
    {
        return [

            'nombre' => $this->faker->optional()->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']), // Valores comunes o nulo
            'descripcion' => $this->faker->optional()->sentence(10), // Descripción aleatoria o nula
        ];
    }
}
