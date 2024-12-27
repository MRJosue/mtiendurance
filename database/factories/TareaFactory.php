<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Tarea;
use App\Models\Proyecto;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tarea>
 */
class TareaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Tarea::class;
    public function definition(): array
    {
        return [
            'proyecto_id' => Proyecto::inRandomOrder()->first()->id,
            'titulo' => $this->faker->sentence(4),
            'descripcion' => $this->faker->paragraph,
            'estatus' => $this->faker->randomElement(['pendiente', 'en progreso', 'completado']),
        ];
    }
}
