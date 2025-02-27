<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    public function up()
    {
        Schema::table('productos', function (Blueprint $table) {
            if (!Schema::hasColumn('productos', 'categoria_id')) {
                $table->unsignedBigInteger('categoria_id')->nullable()->after('id');
            }

            // Intentar agregar la clave foránea solo si no existe
            $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_NAME = 'productos' AND CONSTRAINT_TYPE = 'FOREIGN KEY'");
            $foreignKeyExists = collect($foreignKeys)->contains('CONSTRAINT_NAME', 'productos_categoria_id_foreign');

            if (!$foreignKeyExists) {
                $table->foreign('categoria_id')->references('id')->on('categorias')->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('productos', function (Blueprint $table) {
            // Verificar si la clave foránea existe antes de intentar eliminarla
            $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_NAME = 'productos' AND CONSTRAINT_TYPE = 'FOREIGN KEY'");
            $foreignKeyExists = collect($foreignKeys)->contains('CONSTRAINT_NAME', 'productos_categoria_id_foreign');

            if ($foreignKeyExists) {
                $table->dropForeign(['categoria_id']);
            }

            if (Schema::hasColumn('productos', 'categoria_id')) {
                $table->dropColumn('categoria_id');
            }
        });
    }
};
