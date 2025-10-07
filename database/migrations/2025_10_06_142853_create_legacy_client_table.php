<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client', function (Blueprint $table) {
            // Motor y collation como en el esquema antiguo
            $table->engine = 'MyISAM';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->charset = 'utf8mb4';

            $table->increments('client_id');

            // NOTA: MySQL no permite DEFAULT en LONGTEXT (omitido)
            $table->longText('name');
            $table->longText('email');
            $table->longText('user');
            $table->longText('password');

            $table->longText('tipo')->nullable();
            $table->longText('credito')->nullable();
            $table->longText('plazos')->nullable();
            $table->longText('address')->nullable();
            $table->longText('phone')->nullable();
            $table->longText('company')->nullable();
            $table->longText('website')->nullable();
            $table->longText('skype_id')->nullable();
            $table->longText('facebook_profile_link')->nullable();
            $table->longText('linkedin_profile_link')->nullable();
            $table->longText('twitter_profile_link')->nullable();
            $table->longText('short_note')->nullable();

            $table->string('chat_status', 20)->default('offline');
            $table->integer('super')->default(0);
            $table->integer('super_id')->default(0);
            $table->integer('aprobar')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client');
    }
};
