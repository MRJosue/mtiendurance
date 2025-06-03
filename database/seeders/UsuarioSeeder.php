<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Sucursal;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * 1) Para cada empresa creada, vamos a:
         *    a) Crear un cliente principal (rol = 'cliente_principal').
         *    b) Crear N clientes subordinados (rol = 'cliente_subordinado') y asignarlos a sucursales.
         */

        Empresa::all()->each(function (Empresa $empresa) {
            // --- (a) Cliente principal ---
            $principal = User::create([
                'name'          => $empresa->nombre . ' Principal',
                'email'         => strtolower(str_replace(' ', '', $empresa->nombre)) . '@ejemplo.com',
                'password'      => Hash::make('password123'),
                'rol'           => 'cliente_principal',
                'empresa_id'    => $empresa->id,
                'sucursal_id'   => null, // No tiene sucursal directa
                'email_verified_at' => now(),
            ]);

            // --- (b) Clientes subordinados ---
            // Obtenemos las sucursales de esta empresa
            $sucursales = Sucursal::where('empresa_id', $empresa->id)->get();
            if ($sucursales->isEmpty()) {
                return;
            }

            // Creamos entre 5 y 8 subordinados para esta empresa
            $numSub = rand(5, 8);

            for ($i = 1; $i <= $numSub; $i++) {
                // Elegir una sucursal aleatoria de esta empresa
                $unaSucursal = $sucursales->random();

                $subordinado = User::create([
                    'name'          => $empresa->nombre . " Subordinado {$i}",
                    'email'         => strtolower($empresa->nombre) . "_sub{$i}@ejemplo.com",
                    'password'      => Hash::make('password123'),
                    'rol'           => 'cliente_subordinado',
                    // Lo vinculamos directamente por la clave foránea (si usas campo sucursal_id en users)
                    'sucursal_id'   => $unaSucursal->id,
                    // Como es subordinado, su empresa_id puede ser null o heredarse:
                    // Si tu lógica necesita empresa_id en users, podrías rellenarla:
                    'empresa_id'    => $empresa->id,
                    'email_verified_at' => now(),
                ]);

                /*
                 * Si en lugar de usar el campo sucursal_id directo en users
                 * prefieres usar la tabla pivote, descomenta lo siguiente y
                 * comenta la línea 'sucursal_id' en el create() anterior:
                 *
                 * $subordinado->sucursales()->attach($unaSucursal->id);
                 */
            }
        });
    }
}