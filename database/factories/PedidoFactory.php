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
        $proyecto = Proyecto::factory()->create();
        $producto = Producto::factory()->create();
        $cliente = Cliente::factory()->create();
        

        
        return [
            'proyecto_id' => $proyecto->id,
            'producto_id' => $producto->id,
            'cliente_id' => $cliente->id,
            'fecha_creacion' => now(),
            'total' => $this->faker->randomFloat(2, 100, 10000),
            'estatus' => $this->faker->randomElement(['pendiente', 'procesado', 'cancelado']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
