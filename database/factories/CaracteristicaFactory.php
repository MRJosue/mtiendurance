<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Caracteristica;
use App\Models\Producto; // Importa el modelo Producto
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Caracteristica>
 */
class CaracteristicaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Caracteristica::class;

    public function definition(): array
    {
        return [

            // 'producto_id' => Producto::factory(), // Crea un producto y asigna su ID
            'nombre' => $this->faker->word, // Genera un nombre aleatorio para la característica
            'pasos' => 1,
            'minutoPaso' => 1,
            'valoru' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
