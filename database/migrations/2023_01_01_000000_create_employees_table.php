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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('employee_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->string('mobile_no')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('age')->nullable();
            $table->string('marital')->nullable();
            $table->string('gender')->nullable();
            $table->date('joining_date')->nullable();
            $table->integer('designation_id')->nullable();
            $table->integer('department_id')->nullable();
            $table->integer('reporting_manager_id')->nullable();
            $table->date('date_of_exit')->nullable();
            $table->string('experience')->nullable();
            $table->string('employment_type')->nullable();
            $table->string('employee_status')->nullable();
            $table->string('source_of_hire')->nullable();
            $table->integer('referrer_id')->nullable();
            $table->string('image')->nullable();
            $table->integer('role_id')->nullable();
            $table->string('health_status')->nullable();
            $table->integer('enteredbyid')->nullable();
            $table->integer('organisation_id')->nullable();
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
        Schema::dropIfExists('employees');
    }
};
