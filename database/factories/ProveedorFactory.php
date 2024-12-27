<?php

namespace Database\Factories;

use App\Models\Proveedor;
use App\Models\User; // Importa el modelo de usuario
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Proveedor>
 */
class ProveedorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Proveedor::class;

    public function definition(): array
    {
        return [
            'usuario_id' => User::factory(), // Genera un usuario y obtiene su ID
            'nombre_empresa' => $this->faker->company,
            'contacto_principal' => $this->faker->name,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
