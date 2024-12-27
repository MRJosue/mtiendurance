<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PedidoTalla;
use App\Models\Pedido;
use App\Models\Talla;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PedidoTalla>
 */
class PedidoTallaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = PedidoTalla::class;


    public function definition(): array
    {
        return [
            'pedido_id' => Pedido::factory(), // Genera una instancia de Pedido
            'talla_id' => Talla::factory(),   // Genera una instancia de Talla
            'cantidad' => $this->faker->numberBetween(1, 100), // Genera una cantidad aleatoria
        ];
    }
}
