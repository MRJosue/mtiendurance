<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class DatosInicialesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // DB::unprepared(file_get_contents(database_path('seeders/SQL/datos_iniciales.sql')));
        DB::unprepared(file_get_contents(database_path('seeders/SQL/nuevopermisos.sql')));

        // $sql = file_get_contents(database_path('seeders/SQL/ActualizaPermisos.sql'));
        // DB::unprepared($sql);
        
        DB::unprepared(file_get_contents(database_path('seeders/SQL/LEGACY.sql')));
        DB::unprepared(file_get_contents(database_path('seeders/SQL/LastProyect.sql')));
        DB::unprepared(file_get_contents(database_path('seeders/SQL/ProjectFiles.sql')));

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
