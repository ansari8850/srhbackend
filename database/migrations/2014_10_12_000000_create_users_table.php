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
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('salutation', 10)->nullable();
            $table->string('login_type', 20)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('email', 255)->nullable()->unique();
            $table->string('mobile_no', 15)->nullable();
            $table->string('firebase_uid', 255)->nullable();
            $table->string('work_phone', 20)->nullable();
            $table->string('primary_organisation', 20)->nullable();
            $table->string('active_organisation', 20)->nullable();
            $table->smallInteger('is_disabled')->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('c_password', 100)->nullable();
            $table->string('password', 255)->nullable();
            $table->rememberToken();
            $table->string('organisation_id', 20)->nullable();
            $table->smallInteger('is_email_verified')->default(0);
            $table->string('email_token', 100)->nullable();
            $table->integer('enteredbyid')->default(0);
            $table->smallInteger('is_vendor')->default(0);
            $table->smallInteger('is_customer')->default(0);
            $table->smallInteger('is_employee')->default(0);
            $table->tinyInteger('is_agent')->default(0);
            $table->string('gender', 20)->nullable();
            $table->integer('shift_id')->default(0);
            $table->enum('customer_type', ['Business', 'Individual'])->default('Business');
            $table->string('company_name', 100)->nullable();
            $table->string('display_name', 100)->nullable();
            $table->string('pan_no', 20)->nullable();
            $table->integer('payment_terms')->default(0);
            $table->string('gst_no', 20)->nullable();
            $table->string('place_of_supply', 50)->nullable();
            $table->smallInteger('tax_preference')->default(0);
            $table->string('website', 100)->nullable();
            $table->string('currency', 10)->nullable();
            $table->text('remarks')->nullable();
            $table->text('custom_fields')->nullable();
            $table->tinyInteger('active')->default(1);
            $table->decimal('balance', 12, 2)->default(0.00);
            $table->string('registration_type', 100)->nullable();
            $table->longText('upload_documents')->nullable();
            $table->string('opening_balance', 100)->nullable();
            $table->string('department', 100)->nullable();
            $table->string('designation', 100)->nullable();
            $table->string('customer_note', 191)->nullable();
            $table->integer('otp')->default(0);
            $table->enum('otp_verify', ['0', '1'])->default('0');
            $table->integer('role_id')->default(0);
            $table->integer('subscription_id')->default(0);
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
