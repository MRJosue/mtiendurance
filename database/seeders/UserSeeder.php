<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Usuarios de prueba con contraseñas simples
        $adminUser = User::create([
            'name' => 'german User',
            'email' => 'german@mtiendurance.com',
            'password' => Hash::make('password123'), // Contraseña simple
          
           
        ]);

        $adminUser->assignRole('admin');

                // Usuarios de prueba con contraseñas simples
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'ingjosue.cardona@gmail.com',
            'password' => Hash::make('password123'), // Contraseña simple
           
           
        ]);

        $adminUser->assignRole('admin');
        // Usuarios de prueba con contraseñas simples
        $adminUser = User::create([
            'name' => 'Carlos Prueba',
            'email' => 'carlos@mtiendurance.com',
            'password' => Hash::make('password123'), // Contraseña simple
           
           
        ]);
        $adminUser->assignRole('admin');
        // ------------------------------------------------------------------------------------
        // 2. Recuperar TODOS los roles que ya existen en la tabla `roles` de Spatie
        // ------------------------------------------------------------------------------------
        $roles = Role::all(); // esto traerá todos los registros de la tabla `roles`

        // ------------------------------------------------------------------------------------
        // 3. Por cada rol en la BD, crear dos usuarios de prueba y asignarles ese rol
        // ------------------------------------------------------------------------------------
        foreach ($roles as $rol) {
            for ($i = 1; $i <= 2; $i++) {
                $user = User::create([
                    'name' => ucfirst($rol->name) . " User $i",
                    'email' => strtolower($rol->name) . "$i@example.com",
                    'password' => Hash::make('password123'),
                ]);

                // Asignar el rol recuperado
                $user->assignRole($rol);
            }
        }


    }
}
