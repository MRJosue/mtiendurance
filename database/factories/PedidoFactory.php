<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\Proyecto;
use App\Models\Producto;
use Illuminate\Support\Str;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pedido>
 */
class PedidoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Pedido::class;

    public function definition(): array
    {
        // return [
        //     'id' => Str::uuid(), // Genera un UUID para el pedido
        //     'proyecto_id' => Proyecto::factory()->create()->id, // Crea un Proyecto válido
        //     'producto_id' => Producto::factory()->create()->id, // Crea un Producto válido
        //     'cliente_id' => Cliente::factory()->create()->id,   // Crea un Cliente válido
        //     'fecha_creacion' => now(),
        //     'total' => $this->faker->randomFloat(2, 100, 10000), // Genera un total aleatorio
        //     'estatus' => $this->faker->randomElement(['pendiente', 'procesado', 'cancelado']),
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ];

        return [


            'proyecto_id' => Proyecto::factory()->create()->id,
            'producto_id' => Producto::factory()->create()->id,
            'cliente_id' => Cliente::factory()->create()->id,

            'fecha_creacion' => now(),
            'total' => $this->faker->randomFloat(2, 100, 10000),
            'estatus' => $this->faker->randomElement(['pendiente', 'procesado', 'cancelado']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
