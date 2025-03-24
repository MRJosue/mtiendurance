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
        $user =  Role::create(['name' => 'cliente']);
        $user =  Role::create(['name' => 'proveedor']);
        $user =  Role::create(['name' => 'estaf']);
        $user =  Role::create(['name' => 'diseñador']);
        $user =  Role::create(['name' => 'jefediseñador']);
        $user =  Role::create(['name' => 'operador']);

        $admin->givePermissionTo(['manage users', 'edit profile']);
        $user->givePermissionTo(['edit profile']);
    }
}
