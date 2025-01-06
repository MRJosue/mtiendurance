<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PedidoOpcion;
use App\Models\Pedido;
use App\Models\Opcion;
use Illuminate\Support\Str;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PedidoOpcion>
 */
class PedidoOpcionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = PedidoOpcion::class;

    public function definition(): array
    {
        return [

            'pedido_id' => Pedido::factory(), // Genera un pedido válido
            'opcion_id' => Opcion::factory(), // Genera una opción válida
            'valor' => $this->faker->word(), // Genera un valor aleatorio
        ];
    }
}
