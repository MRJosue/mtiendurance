<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\User;



class DireccionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();

        foreach ($users as $user) {
            // Crear direcciones fiscales
            DB::table('direcciones_fiscales')->insert([
                [
                    'usuario_id' => $user->id,
                    'rfc' => 'RFC123456789',
                    'calle' => 'Calle Principal 123',
                    'ciudad_id' => 1, // Relacionar con Ciudad de Ejemplo
                    'estado_id' => 1, // Relacionar con Estado de Ejemplo
                    'pais_id' => 1, // Relacionar con México
                    'codigo_postal' => '12345',
                    'flag_default' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'usuario_id' => $user->id,
                    'rfc' => 'RFC987654321',
                    'calle' => 'Avenida Secundaria 456',
                    'ciudad_id' => 2, // Relacionar con Ciudad Alternativa
                    'estado_id' => 2, // Relacionar con Estado Alternativo
                    'pais_id' => 1, // Relacionar con México
                    'codigo_postal' => '67890',
                    'flag_default' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            // Crear direcciones de entrega
            DB::table('direcciones_entrega')->insert([
                [
                    'usuario_id' => $user->id,
                    'nombre_contacto' => 'Contacto Principal',
                    'nombre_empresa'=>'Nombre de empresa',
                    'calle' => 'Calle Entrega 123',
                    'ciudad_id' => 3, // Relacionar con Ciudad de Entrega
                    'estado_id' => 3, // Relacionar con Estado de Entrega
                    'pais_id' => 1, // Relacionar con México
                    'codigo_postal' => '54321',
                    'telefono' => '1234567890',
                    'flag_default' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'usuario_id' => $user->id,
                    'nombre_contacto' => 'Contacto Alternativo',
                    'nombre_empresa'=>'Nombre de empresa',
                    'calle' => 'Avenida Entrega 456',
                    'ciudad_id' => 4, // Relacionar con Ciudad Alternativa
                    'estado_id' => 4, // Relacionar con Estado Alternativo
                    'pais_id' => 1, // Relacionar con México
                    'codigo_postal' => '98765',
                    'telefono' => '0987654321',
                    'flag_default' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }
}
