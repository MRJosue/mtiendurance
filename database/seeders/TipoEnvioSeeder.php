<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class TipoEnvioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tipo_envio')->insert([
            [
                'nombre' => 'Terrestre Local',
                'descripcion' => 'Envío terrestre dentro de la misma localidad.',
                'dias_envio' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Terrestre Nacional',
                'descripcion' => 'Envío terrestre a nivel nacional.',
                'dias_envio' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Terrestre Extranjero',
                'descripcion' => 'Envío terrestre a otros países.',
                'dias_envio' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Aire Nacional',
                'descripcion' => 'Envío aéreo a nivel nacional.',
                'dias_envio' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Aire Extranjero',
                'descripcion' => 'Envío aéreo a otros países.',
                'dias_envio' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
