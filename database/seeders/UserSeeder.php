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
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'), // Contraseña simple
          
           
        ]);

                // Usuarios de prueba con contraseñas simples
        User::create([
            'name' => 'Admin User',
            'email' => 'ingjosue.cardona@gmail.com',
            'password' => Hash::make('password123'), // Contraseña simple
           
           
        ]);

        // Usuarios de prueba con contraseñas simples
        User::create([
            'name' => 'Carlos Prueba',
            'email' => 'carlos@mtiendurance.com',
            'password' => Hash::make('password123'), // Contraseña simple
           
           
        ]);

        User::create([
            'name' => 'Staff User',
            'email' => 'staff@example.com',
            'password' => Hash::make('password123'), // Contraseña simple
           
          
        ]);

        // Definir los roles disponibles
        $roles = ['admin', 'cliente', 'proveedor', 'estaf', 'diseñador', 'jefediseñador', 'operador'];

        foreach ($roles as $roleName) {
            // Buscar o crear el rol
            $role = Role::where('name', $roleName)->first();

            // Crear dos usuarios por cada rol
            for ($i = 1; $i <= 2; $i++) {
                $user = User::create([
                    'name' => ucfirst($roleName) . " User $i",
                    'email' => strtolower($roleName) . "$i@example.com",
                    'password' => Hash::make('password123'),
                ]);

                // Asignar el rol al usuario
                $user->assignRole($role);
            }
        }

        // Usuarios adicionales generados automáticamente
        User::factory(10)->create();
    }
}
