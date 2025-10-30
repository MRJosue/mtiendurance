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
        Schema::create('staff', function (Blueprint $table) {
            $table->id('staff_id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('user')->unique();
            $table->string('password');
            $table->unsignedBigInteger('account_role_id');
            $table->string('phone')->nullable();
            $table->string('skype_id')->nullable();
            $table->string('facebook_profile_link')->nullable();
            $table->string('twitter_profile_link')->nullable();
            $table->string('linkedin_profile_link')->nullable();
            $table->string('chat_status', 20)->default('offline');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
