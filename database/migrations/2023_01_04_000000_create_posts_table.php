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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('user_name')->nullable();
            $table->unsignedBigInteger('field_id')->nullable();
            $table->string('field_name')->nullable();
            $table->string('post_type')->nullable();
            $table->unsignedBigInteger('post_type_id')->nullable();
            $table->string('location')->nullable();
            $table->string('date')->nullable();
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('auto_delete_date')->nullable();
            $table->string('status')->default('active');
            $table->integer('is_disabled')->default(0); // Added for auto-delete logic
            $table->string('title')->nullable();
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
        Schema::dropIfExists('posts');
    }
};
