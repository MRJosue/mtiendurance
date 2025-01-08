<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Chat;
use App\Models\Proyecto;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chat>
 */
class ChatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'proyecto_id' => Proyecto::factory(), // Crea un proyecto si no existe
            'fecha_creacion' => now(),
        ];
    }
}
