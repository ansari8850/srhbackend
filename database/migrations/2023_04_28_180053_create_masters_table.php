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
        Schema::create('masters', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->string('name')->nullable();
            $table->integer('parent_id')->nullable();
            $table->json('extra_data')->nullable();
            $table->string('field_id')->nullable();
            $table->string('location')->nullable();
            $table->string('sub_type')->nullable();
            $table->string('field_name')->nullable();
            $table->string('status')->default('1');
            $table->integer('is_disabled')->default(0);
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
        Schema::dropIfExists('masters');
    }
};
