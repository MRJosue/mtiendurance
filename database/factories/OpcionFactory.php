<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Opcion;
use App\Models\Caracteristica; // Importa el modelo Caracteristica
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Opcion>
 */
class OpcionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Opcion::class;

    public function definition(): array
    {
        return [

            'caracteristica_id' => Caracteristica::factory(), // Genera una característica y asigna su ID
            'valor' => $this->faker->word, // Genera un valor aleatorio
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
