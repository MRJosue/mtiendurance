<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PedidoCaracteristica;
use App\Models\Pedido;
use App\Models\Caracteristica;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PedidoCaracteristica>
 */
class PedidoCaracteristicaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = PedidoCaracteristica::class;

    public function definition(): array
    {
        return [
            'pedido_id' => Pedido::inRandomOrder()->first()->id,
            'caracteristica_id' => Caracteristica::inRandomOrder()->first()->id,
        ];
    }
}
