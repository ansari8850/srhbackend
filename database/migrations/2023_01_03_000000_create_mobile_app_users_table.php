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
            $table->string('name', 100)->nullable();
            $table->string('agent_no', 50)->nullable();
            $table->string('last_name', 200)->nullable();
            $table->string('display_name', 200)->nullable();
            $table->integer('agent_id')->default(0);
            $table->string('mobile_no', 50)->nullable();
            $table->enum('login_type', ['Admin', 'User', 'Agent'])->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('date_of_birth', 20)->nullable();
            $table->string('department', 191)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('role', 50)->nullable();
            $table->string('password', 191)->nullable();
            $table->string('c_password', 100)->nullable();
            $table->string('work_phone', 200)->nullable();
            $table->string('contact_person_name', 200)->nullable();
            $table->string('business_leagal_name', 200)->nullable();
            $table->string('user_type_id', 200)->nullable();
            $table->string('emr_mobile_no', 50)->nullable();
            $table->string('field', 100)->nullable();
            $table->string('user_type', 191)->nullable();
            $table->string('tax_preference', 200)->nullable();
            $table->string('gst_no', 200)->nullable();
            $table->string('pan_no', 200)->nullable();
            $table->string('website', 200)->nullable();
            $table->string('registration_type', 200)->nullable();
            $table->text('bio')->nullable();
            $table->string('sub_field', 100)->nullable();
            $table->text('photo')->nullable();
            $table->string('education', 100)->nullable();
            $table->text('address')->nullable();
            $table->text('remark')->nullable();
            $table->text('attachments')->nullable();
            $table->string('reporting_manager', 50)->nullable();
            $table->string('employment_type', 50)->nullable();
            $table->tinyInteger('is_disabled')->default(0);
            $table->string('date', 191)->nullable();
            $table->string('country_id', 191)->nullable();
            $table->string('state_id', 191)->nullable();
            $table->string('city_id', 191)->nullable();
            $table->string('street_1', 191)->nullable();
            $table->string('street_2', 191)->nullable();
            $table->string('zip_code', 191)->nullable();
            $table->string('company_name', 191)->nullable();
            $table->string('contact_person', 191)->nullable();
            $table->string('work_email', 191)->nullable();
            $table->string('field_name', 191)->nullable();
            $table->integer('otp')->default(0);
            $table->enum('otp_verify', ['0', '1'])->default('0');
            $table->text('description')->nullable();
            $table->string('status', 191)->nullable();
            $table->string('firebase_uid', 255)->nullable();
            $table->string('fcm_token', 255)->nullable();
            $table->string('device_token', 255)->nullable();
            $table->string('nominee', 50)->nullable();
            $table->tinyInteger('is_subscription')->default(0);
            $table->integer('subscription_id')->default(0);
            $table->string('location', 255)->nullable();
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
