<?php

namespace Database\Factories;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cliente>
 */
class ClienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Cliente::class;

    public function definition(): array
    {
        return [
            'usuario_id' => User::factory(), // Genera un usuario relacionado automáticamente
            'nombre_empresa' => $this->faker->company(),
            'contacto_principal' => $this->faker->name(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
