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
                    'user_id' => $user->id,
                    'rfc' => 'RFC123456789',
                    'calle' => 'Calle Principal 123',
                    'ciudad' => 'Ciudad de Ejemplo',
                    'estado' => 'Estado de Ejemplo',
                    'codigo_postal' => '12345',
                    'flag_default' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => $user->id,
                    'rfc' => 'RFC987654321',
                    'calle' => 'Avenida Secundaria 456',
                    'ciudad' => 'Ciudad Alternativa',
                    'estado' => 'Estado Alternativo',
                    'codigo_postal' => '67890',
                    'flag_default' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            // Crear direcciones de entrega
            DB::table('direcciones_entrega')->insert([
                [
                    'user_id' => $user->id,
                    'nombre_contacto' => 'Contacto Principal',
                    'calle' => 'Calle Entrega 123',
                    'ciudad' => 'Ciudad de Entrega',
                    'estado' => 'Estado de Entrega',
                    'codigo_postal' => '54321',
                    'telefono' => '1234567890',
                    'flag_default' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => $user->id,
                    'nombre_contacto' => 'Contacto Alternativo',
                    'calle' => 'Avenida Entrega 456',
                    'ciudad' => 'Ciudad Alternativa',
                    'estado' => 'Estado Alternativo',
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