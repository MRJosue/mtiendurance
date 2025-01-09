<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Usuarios de prueba con contraseñas simples
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'), // Contraseña simple
            'rol_id' => 1, // Ajusta según el ID de rol para administración
            'tipo_usuario' => 'ADMINISTRACION',
        ]);

        // Usuarios de prueba con contraseñas simples
        User::create([
            'name' => 'Admin User',
            'email' => 'test@gmail.com',
            'password' => Hash::make('password123'), // Contraseña simple
            'rol_id' => 1, // Ajusta según el ID de rol para administración
            'tipo_usuario' => 'ADMINISTRACION',
        ]);

        User::create([
            'name' => 'Staff User',
            'email' => 'staff@example.com',
            'password' => Hash::make('password123'), // Contraseña simple
            'rol_id' => 2, // Ajusta según el ID de rol para staff
            'tipo_usuario' => 'STAFF',
        ]);


        // Usuarios adicionales generados automáticamente
        User::factory(10)->create();
    }
}
