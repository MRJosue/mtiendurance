<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Empresa;
use App\Models\Sucursal;

class SucursalSeeder extends Seeder
{
    public function run(): void
    {
        // Recorre cada empresa existente y crea entre 2 y 4 sucursales para cada una
        Empresa::all()->each(function (Empresa $empresa) {
            // Número aleatorio de sucursales (2 a 4) por empresa
            $cantidad = rand(2, 4);

            Sucursal::factory($cantidad)
                ->forEmpresa($empresa->id)
                ->create();
        });
    }
}