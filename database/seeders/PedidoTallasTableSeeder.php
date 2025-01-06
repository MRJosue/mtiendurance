<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pedido;
use App\Models\PedidoTalla;
use App\Models\Talla;

class PedidoTallasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    // Crea tallas válidas
    $talla = Talla::factory()->create();
    $this->command->info('talla creado con ID: ' . $talla->id);
    // Crea pedidos válidos
    $pedido = Pedido::factory()->create();
    $this->command->info('pedido creado con ID: ' . $pedido->id);

    //PedidoTalla::factory(5)->create();
    // Crea registros en pedido_tallas relacionados al pedido y la talla
    PedidoTalla::factory()->count(1)->create([
            'pedido_id' => $pedido->id,
            'talla_id' => $talla->id,
           'cantidad' => 5,
        ]);

    }
}
