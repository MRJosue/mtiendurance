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

        file_get_contents('/home/mtiadmin/public_html/portal.mti/mtiendurance/database/seeders/sql/datos_iniciales.sql');

        // DB::unprepared(file_get_contents(database_path('seeders/sql/datos_iniciales.sql')));
        DB::unprepared(file_get_contents(database_path('seeders/sql/nuevopermisos.sql')));

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
