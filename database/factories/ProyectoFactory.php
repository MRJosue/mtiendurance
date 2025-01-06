<?php

namespace Database\Factories;
use App\Models\Proyecto;
use App\Models\User;
use App\Models\Producto;
use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Proyecto>
 */
class ProyectoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Proyecto::class;
    public function definition(): array
    {
        // return [
        //     'id' => Str::uuid(), // Genera un UUID para la clave primaria
        //     'usuario_id' => User::factory()->create()->id, // Crea un usuario relacionado
        //     'nombre' => $this->faker->words(3, true), // Genera un nombre aleatorio
        //     'descripcion' => $this->faker->paragraph(), // Descripción aleatoria
        //     'estado' => $this->faker->randomElement([
        //         'PENDIENTE', 'APROBADO', 'PROGRAMADO', 'IMPRESIÓN',
        //         'PRODUCCIÓN', 'COSTURA', 'ENTREGA', 'FACTURACIÓN', 'COMPLETADO'
        //     ]),
        //     'fecha_creacion' => now(),
        //     'fecha_entrega' => $this->faker->optional()->date(), // Fecha aleatoria opcional
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ];

        return [
            'usuario_id' => User::factory()->create()->id, // Crea un usuario válido
            'nombre' => $this->faker->words(3, true),
            'descripcion' => $this->faker->paragraph(),
            'estado' => 'PENDIENTE',
            'fecha_creacion' => now(),
            'fecha_entrega' => $this->faker->optional()->date(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
