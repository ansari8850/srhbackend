<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mobile_app_users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('display_name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('mobile_no')->nullable();
            $table->string('password')->nullable();
            $table->string('c_password')->nullable();
            $table->string('login_type')->default('User');
            $table->string('otp')->nullable();
            $table->integer('otp_verify')->default(0);
            $table->string('firebase_uid')->nullable();
            $table->string('apple_id')->nullable();
            $table->integer('is_disabled')->default(0);
            $table->string('image')->nullable();
            $table->string('address')->nullable();
            $table->integer('country_id')->nullable();
            $table->integer('state_id')->nullable();
            $table->integer('city_id')->nullable();
            $table->integer('agent_id')->nullable();
            $table->string('user_type')->nullable(); // Added for post filtering/stats
            $table->string('fcm_token')->nullable(); // Added for notifications
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mobile_app_users');
    }
};
