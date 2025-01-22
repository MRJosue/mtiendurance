<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CiudadesTipoEnvioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Obtener todas las ciudades y tipos de envío
        $ciudades = DB::table('ciudades')->pluck('id');
        $tiposEnvio = DB::table('tipo_envio')->pluck('id');

        // Asignar todos los tipos de envío a todas las ciudades
        $data = [];
        foreach ($ciudades as $ciudadId) {
            foreach ($tiposEnvio as $tipoEnvioId) {
                $data[] = [
                    'ciudad_id' => $ciudadId,
                    'tipo_envio_id' => $tipoEnvioId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insertar datos en la tabla ciudades_tipo_envio
        DB::table('ciudades_tipo_envio')->insert($data);
    }
}
