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
        Schema::create('clientsup', function (Blueprint $table) {
            $table->id('clientsup_id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('user')->unique();
            $table->string('password');
            $table->string('tipo')->nullable();
            $table->string('credito')->nullable();
            $table->string('plazos')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('website')->nullable();
            $table->string('skype_id')->nullable();
            $table->string('facebook_profile_link')->nullable();
            $table->string('linkedin_profile_link')->nullable();
            $table->string('twitter_profile_link')->nullable();
            $table->text('short_note')->nullable();
            $table->string('chat_status', 20)->default('offline');
            $table->boolean('super')->default(false);
            $table->unsignedBigInteger('super_id')->default(0);
            $table->boolean('aprobar')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientsup');
    }
};
