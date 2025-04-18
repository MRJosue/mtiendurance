<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Str;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Caracteristica;
use App\Models\Opcion;



class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        //$this->call(UserSeeder::class);

        $this->call([

            RolePermissionSeeder::class,
            UserSeeder::class,
            PaisesEstadosCiudadesSeeder::class,
            DireccionesSeeder::class,
            TipoEnvioSeeder::class,
            CiudadesTipoEnvioSeeder::class,
            ClientesTableSeeder::class,
            ProveedoresTableSeeder::class,
            
            CategoriasTableSeeder::class,
            ProductosTableSeeder::class,
            CaracteristicasTableSeeder::class,
            OpcionesTableSeeder::class,


           // ProyectosTableSeeder::class,
            TallasTableSeeder::class,
          
            PermissionSeeder::class,

           // ProyectoDefinidoSeeder::class,
           // PedidosTableSeeder::class,
           // PedidoTallasTableSeeder::class,
           // PedidoOpcionesTableSeeder::class,
           // PedidoCaracteristicasTableSeeder::class,

            //PedidoCaracteristicasTableSeeder::class,
            ChatSeeder::class,

            TareasTableSeeder::class,




        ]);
    }
}
