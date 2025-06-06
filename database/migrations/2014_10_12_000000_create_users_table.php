<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            //$table->enum('tipo_usuario', ['ADMINISTRACION', 'STAFF', 'CLIENTES', 'PROVEEDORES'])->default('CLIENTES');
            //$table->unsignedBigInteger('rol_id')->default(1);
            $table->timestamps();


            //$table->foreign('rol_id')->references('id')->on('roles')->onDelete('cascade');
            
        });

        
    }


    public function down(): void
    {

        Schema::dropIfExists('users');
    }
};
