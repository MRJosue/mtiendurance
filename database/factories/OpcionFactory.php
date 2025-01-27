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

     // test
    protected $model = Opcion::class;

    public function definition(): array
    {
        return [

            // 'caracteristica_id' => Caracteristica::factory(), // Genera una característica y asigna su ID
            'nombre' => $this->faker->word, // Genera un valor aleatorio
            // 'valor' => $this->faker->word, // Genera un valor aleatorio
            'valoru' => 1, // Puedes cambiar esto si necesitas un valor específico
            'pasos' => 1,
            'minutoPaso' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
