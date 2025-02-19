<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\User;


class ClientesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
     $users = User::all();

     foreach ($users as $user) {
        if($user->tipo_usuario == 'CLIENTES'){
            Cliente::create([
                'usuario_id' => $user->id,
                'nombre_empresa' => 'Empresa SA de CV', // Puedes cambiar esto si necesitas un valor específico
                'contacto_principal' => 'User Contacto',
                'telefono' => '554717565556',
                'email' => 'Email'.$user->id.'@contacto.com'

            ]);

        }
     }
    }

    // public function run(): void
    // {
    //     Cliente::factory(10)->create();
    // }
}
