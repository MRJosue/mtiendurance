<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Crear permisos
        Permission::create(['name' => 'manage users']);
        Permission::create(['name' => 'edit profile']);

        // Crear roles y asignar permisos
        $admin = Role::create(['name' => 'admin']);
        $user = Role::create(['name' => 'user']);

        $admin->givePermissionTo(['manage users', 'edit profile']);
        $user->givePermissionTo(['edit profile']);
    }
}
