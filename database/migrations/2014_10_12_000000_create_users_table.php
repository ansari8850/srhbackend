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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('mobile_no')->nullable();
            $table->integer('is_employee')->default(0);
            $table->integer('role_id')->nullable();
            $table->string('password');
            $table->string('otp')->nullable();
            $table->integer('otp_verify')->default(0);
            $table->integer('enteredbyid')->nullable();
            $table->integer('organisation_id')->nullable();
            $table->integer('is_disabled')->default(0);
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
