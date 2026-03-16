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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->default(0);
            $table->string('payment_id', 255)->nullable();
            $table->string('order_id', 255)->nullable();
            $table->string('signature', 255)->nullable();
            $table->integer('subscription_plan_id')->default(0);
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->string('currency', 10)->default('INR');
            $table->string('status', 50)->nullable();
            $table->string('method', 50)->nullable();
            $table->text('description')->nullable();
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
        Schema::dropIfExists('payments');
    }
};
