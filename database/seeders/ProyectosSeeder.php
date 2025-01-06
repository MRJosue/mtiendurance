<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ProyectosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Seed 10 categorías
        $categorias = [];
        for ($i = 1; $i <= 10; $i++) {
            $categorias[] = [
                'id' => $i,
                'nombre' => 'Categoría ' . $i,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        DB::table('categorias')->insert($categorias);

        // Seed 50 productos relacionados a categorías
        $productos = [];
        for ($i = 1; $i <= 50; $i++) {
            $productos[] = [
                'id' => $i,
                'categoria_id' => rand(1, 10),
                'nombre' => 'Producto ' . $i,

                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        DB::table('productos')->insert($productos);

        // Seed 100 características relacionadas a productos
        $caracteristicas = [];
        for ($i = 1; $i <= 100; $i++) {
            $caracteristicas[] = [
                'id' => $i,
                'producto_id' => rand(1, 50),
                'nombre' => 'Característica ' . $i,

                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        DB::table('caracteristicas')->insert($caracteristicas);

        // Seed 200 opciones relacionadas a características
        $opciones = [];
        for ($i = 1; $i <= 200; $i++) {
            $opciones[] = [
                'id' => $i,
                'caracteristica_id' => rand(1, 100),
                'nombre' => 'Opción ' . $i,

                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        DB::table('opciones')->insert($opciones);

        // Seed 20 clientes
        $clientes = [];
        for ($i = 1; $i <= 20; $i++) {
            $clientes[] = [
                'id' => $i,
                'nombre' => 'Cliente ' . $i,
                'email' => 'cliente' . $i . '@example.com',
                'telefono' => '555-000' . $i,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        DB::table('clientes')->insert($clientes);

        // Seed 20 proveedores
        $proveedores = [];
        for ($i = 1; $i <= 20; $i++) {
            $proveedores[] = [
                'id' => $i,
                'nombre' => 'Proveedor ' . $i,
                'telefono' => '444-000' . $i,
                'email' => 'proveedor' . $i . '@example.com',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        DB::table('proveedores')->insert($proveedores);

        // Seed 30 pedidos relacionados a clientes y proveedores
        $pedidos = [];
        for ($i = 1; $i <= 30; $i++) {
            $pedidos[] = [
                'id' => $i,
                'cliente_id' => rand(1, 20),
                'proveedor_id' => rand(1, 20),
                'fecha_pedido' => now()->subDays(rand(1, 60)),
                'total' => rand(100, 1000),
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        DB::table('pedidos')->insert($pedidos);

        // Seed 20 proyectos relacionados a pedidos
        $proyectos = [];
        for ($i = 1; $i <= 20; $i++) {
            $proyectos[] = [
                'id' => $i,
                'pedido_id' => rand(1, 30), // Relación con pedidos
                'nombre' => 'Proyecto ' . $i,
                'descripcion' => 'Descripción del Proyecto ' . $i,
                'fecha_inicio' => now()->subDays(rand(1, 30)),
                'fecha_fin' => now()->addDays(rand(10, 60)),
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        DB::table('proyectos')->insert($proyectos);

        // Seed 100 tareas relacionadas a pedidos
        $tareas = [];
        for ($i = 1; $i <= 100; $i++) {
            $tareas[] = [
                'id' => $i,
                'pedido_id' => rand(1, 30),
                'descripcion' => 'Tarea ' . $i . ' para el Pedido',
                'estado' => rand(0, 1) ? 'completada' : 'pendiente',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        DB::table('tareas')->insert($tareas);
    }
}
