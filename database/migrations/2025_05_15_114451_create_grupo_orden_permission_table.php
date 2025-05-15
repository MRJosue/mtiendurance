<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrupoOrdenPermissionTable extends Migration
{
    public function up()
    {
        Schema::create('grupo_orden_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_orden_id')->constrained('grupos_orden')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->integer('orden')->default(0);
            $table->timestamps();

            $table->unique(['grupo_orden_id', 'permission_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('grupo_orden_permission');
    }
}