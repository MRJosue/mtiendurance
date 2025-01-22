<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class PaisesEstadosCiudadesSeeder extends Seeder
{
    public function run()
    {
        // Insertar Países
        $mexicoId = DB::table('paises')->insertGetId(['nombre' => 'México', 'created_at' => now(), 'updated_at' => now()]);
        $usaId = DB::table('paises')->insertGetId(['nombre' => 'Estados Unidos', 'created_at' => now(), 'updated_at' => now()]);

        // Insertar Estados y Ciudades de México
        $estadosMexico = [
            ['nombre' => 'Ciudad de México', 'ciudades' => ['Álvaro Obregón', 'Coyoacán', 'Tlalpan']],
            ['nombre' => 'Jalisco', 'ciudades' => ['Guadalajara', 'Zapopan', 'Tlaquepaque']],
            ['nombre' => 'Nuevo León', 'ciudades' => ['Monterrey', 'San Pedro Garza García', 'Guadalupe']],
            ['nombre' => 'Yucatán', 'ciudades' => ['Mérida', 'Valladolid', 'Progreso']]
        ];

        foreach ($estadosMexico as $estado) {
            $estadoId = DB::table('estados')->insertGetId([
                'pais_id' => $mexicoId,
                'nombre' => $estado['nombre'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            foreach ($estado['ciudades'] as $ciudad) {
                DB::table('ciudades')->insert([
                    'estado_id' => $estadoId,
                    'nombre' => $ciudad,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        // Insertar Estados y Ciudades de Estados Unidos
        $estadosUsa = [
            ['nombre' => 'California', 'ciudades' => ['Los Ángeles', 'San Francisco', 'San Diego']],
            ['nombre' => 'Texas', 'ciudades' => ['Houston', 'Dallas', 'Austin']],
            ['nombre' => 'Nueva York', 'ciudades' => ['Nueva York', 'Buffalo', 'Rochester']],
            ['nombre' => 'Florida', 'ciudades' => ['Miami', 'Orlando', 'Tampa']]
        ];

        foreach ($estadosUsa as $estado) {
            $estadoId = DB::table('estados')->insertGetId([
                'pais_id' => $usaId,
                'nombre' => $estado['nombre'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            foreach ($estado['ciudades'] as $ciudad) {
                DB::table('ciudades')->insert([
                    'estado_id' => $estadoId,
                    'nombre' => $ciudad,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}
