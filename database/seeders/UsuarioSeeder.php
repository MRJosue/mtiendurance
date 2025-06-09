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
        Empresa::all()->each(function (Empresa $empresa) {
            // --- Crear cliente principal ---
            $principal = User::create([
                'name'              => $empresa->nombre . ' Principal',
                'email'             => strtolower(str_replace(' ', '', $empresa->nombre)) . '@ejemplo.com',
                'password'          => Hash::make('password123'),
                'empresa_id'        => $empresa->id,
                'email_verified_at' => now(),
            ]);

            // Asignar rol cliente_principal con Spatie
            $principal->assignRole('cliente_principal');

            // Obtener sucursales disponibles
            $sucursales = Sucursal::where('empresa_id', $empresa->id)->get();
            if ($sucursales->isEmpty()) return;

            $numSub = rand(5, 8);
            $subordinadosIds = [];

            for ($i = 1; $i <= $numSub; $i++) {
                $sucursal = $sucursales->random();

                $subordinado = User::create([
                    'name'              => $empresa->nombre . " Subordinado {$i}",
                    'email'             => strtolower($empresa->nombre) . "_sub{$i}@ejemplo.com",
                    'password'          => Hash::make('password123'),
                    'empresa_id'        => $empresa->id,
                    'sucursal_id'       => $sucursal->id,
                    'email_verified_at' => now(),
                ]);

                // Asignar rol cliente_subordinado con Spatie
                $subordinado->assignRole('cliente_subordinado');

                // Guardar ID en lista para el principal
                $subordinadosIds[] = $subordinado->id;
            }

            // Asignar subordinados al principal (campo json 'subordinados')
            $principal->subordinados = $subordinadosIds;
            $principal->save();
        });
    }
}