<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Empresa;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        // Crea 5 empresas de ejemplo
        Empresa::factory(5)->create();
    }
}