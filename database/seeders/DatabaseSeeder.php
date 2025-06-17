<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\User;

class DatabaseSeeder extends Seeder
{



    public function run(): void
    {
        // $usuarios = User::where('email', '!=', 'admin@mtiendurance.com')->take(5)->get();

        // $now = Carbon::now();

        // foreach ($usuarios as $usuario) {
        //     for ($i = 1; $i <= 3; $i++) {
        //         // Primer tipo: Portagafete
        //         DB::table('pre_proyectos')->insert([
        //             'usuario_id' => $usuario->id,
        //             'direccion_fiscal_id' => 3,
        //             'direccion_fiscal' => 'Álvaro Obregón, Ciudad de México, México',
        //             'direccion_entrega_id' => 4,
        //             'direccion_entrega' => 'Guadalajara, Jalisco, México',
        //             'nombre' => "Preproyecto Portagafete $i de {$usuario->name}",
        //             'descripcion' => 'Preproyecto generado automáticamente (Portagafete)',
        //             'id_tipo_envio' => 1,
        //             'tipo' => 'PROYECTO',
        //             'numero_muestras' => 0,
        //             'estado' => 'PENDIENTE',
        //             'fecha_creacion' => $now,
        //             'fecha_produccion' => '2025-05-16',
        //             'fecha_embarque' => '2025-05-27',
        //             'fecha_entrega' => '2025-05-27',
        //             'categoria_sel' => '{"id":"9","nombre":"Portagafete"}',
        //             'flag_armado' => 1,
        //             'producto_sel' => '{"id":"5","nombre":"Portagafete Standard"}',
        //             'caracteristicas_sel' => '[{"id":2,"nombre":"Tipo de Cinta","flag_seleccion_multiple":false,"opciones":[{"id":"26","nombre":"Satinada","valoru":0}]},{"id":3,"nombre":"Impresion","flag_seleccion_multiple":false,"opciones":[{"id":"60","nombre":"Una Cara","valoru":0}]},{"id":5,"nombre":"Medida","flag_seleccion_multiple":false,"opciones":[{"id":"51","nombre":"14mm","valoru":0}]},{"id":9,"nombre":"Herraje","flag_seleccion_multiple":false,"opciones":[{"id":"10","nombre":"Yoyo de Clip","valoru":0}]},{"id":11,"nombre":"Terminado","flag_seleccion_multiple":false,"opciones":[{"id":"24","nombre":"Costura con Dobladillo","valoru":0}]}]',
        //             'opciones_sel' => '[]',
        //             'total_piezas_sel' => '{"total":100,"detalle_tallas":null}',
        //             'created_at' => $now,
        //             'updated_at' => $now,
        //         ]);

        //         // Segundo tipo: Playeras
        //         DB::table('pre_proyectos')->insert([
        //             'usuario_id' => $usuario->id,
        //             'direccion_fiscal_id' => 4,
        //             'direccion_fiscal' => 'Coyoacán, Ciudad de México, México',
        //             'direccion_entrega_id' => 4,
        //             'direccion_entrega' => 'Guadalajara, Jalisco, México',
        //             'nombre' => "Preproyecto Playeras $i de {$usuario->name}",
        //             'descripcion' => 'Preproyecto generado automáticamente (Playeras)',
        //             'id_tipo_envio' => 2,
        //             'tipo' => 'PROYECTO',
        //             'numero_muestras' => 0,
        //             'estado' => 'PENDIENTE',
        //             'fecha_creacion' => $now,
        //             'fecha_produccion' => '2025-05-01',
        //             'fecha_embarque' => '2025-05-19',
        //             'fecha_entrega' => '2025-05-27',
        //             'categoria_sel' => '{"id":"1","nombre":"Playera"}',
        //             'flag_armado' => 1,
        //             'producto_sel' => '{"id":"13","nombre":"Playera Manga Corta"}',
        //             'caracteristicas_sel' => '[{"id":1,"nombre":"Tipo de Tela","flag_seleccion_multiple":false,"opciones":[{"id":"25","nombre":"Pique","valoru":0}]},{"id":21,"nombre":"Tipo de Cuello","flag_seleccion_multiple":false,"opciones":[{"id":"81","nombre":"Cuello Redondo","valoru":0}]}]',
        //             'opciones_sel' => '[]',
        //             'total_piezas_sel' => '{"total":60,"detalle_tallas":{"1":{"1":10,"2":10,"3":10,"4":0,"5":0,"6":0,"7":0},"2":{"1":10,"2":10,"3":10,"4":0,"5":0,"6":0}}}',
        //             'created_at' => $now,
        //             'updated_at' => $now,
        //         ]);
        //     }
        // }

        $this->call([

            RolePermissionSeeder::class,
            UserSeeder::class,
            PaisesEstadosCiudadesSeeder::class,
          
            TipoEnvioSeeder::class,
            CiudadesTipoEnvioSeeder::class,
            ClientesTableSeeder::class,
            ProveedoresTableSeeder::class,
            
            // CategoriasTableSeeder::class,
            // ProductosTableSeeder::class,
            // CaracteristicasTableSeeder::class,
            // OpcionesTableSeeder::class,


           // ProyectosTableSeeder::class,
           // TallasTableSeeder::class,
          
            PermissionSeeder::class,

           // ProyectoDefinidoSeeder::class,
           // PedidosTableSeeder::class,
           // PedidoTallasTableSeeder::class,
           // PedidoOpcionesTableSeeder::class,
           // PedidoCaracteristicasTableSeeder::class,

            //PedidoCaracteristicasTableSeeder::class,
            ChatSeeder::class,

            TareasTableSeeder::class,

            DatosInicialesSeeder::class,


        ]);

        // // 1. Sembrar empresas
        // $this->call(EmpresaSeeder::class);

        // // 2. Sembrar sucursales (necesita que existan empresas)
        // $this->call(SucursalSeeder::class);

        // // 3. Sembrar usuarios: clientes principales y subordinados
        // $this->call(UsuarioSeeder::class);

        // // Si tienes otros seeders, agrégalos aquí debajo en el orden apropiado
        // $this->call(DireccionesSeeder::class);
    }
}
