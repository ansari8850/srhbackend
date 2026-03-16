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
            $table->string('user_id', 200)->nullable();
            $table->string('user_name', 200)->nullable();
            $table->string('field_id', 200)->nullable();
            $table->string('post_type', 200)->nullable();
            $table->string('field_name', 100)->nullable();
            $table->string('title', 100)->nullable();
            $table->string('location', 200)->nullable();
            $table->string('date', 200)->nullable();
            $table->text('description')->nullable();
            $table->text('rejected_reason')->nullable();
            $table->string('thumbnail', 200)->nullable();
            $table->datetime('auto_delete_date')->nullable();
            $table->string('status', 191)->nullable();
            $table->integer('is_disabled')->default(0);
            $table->string('post_type_id', 200)->nullable();
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
