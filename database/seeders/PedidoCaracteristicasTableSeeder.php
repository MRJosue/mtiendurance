<?php

namespace Database\Seeders;
use App\Models\PedidoCaracteristica;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PedidoCaracteristicasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PedidoCaracteristica::factory(10)->create();
    }
}
