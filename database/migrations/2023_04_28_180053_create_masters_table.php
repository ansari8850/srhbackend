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
            $table->string('type', 255);
            $table->string('name', 255);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->longText('extra_data')->nullable();
            $table->string('field_id', 200)->nullable();
            $table->string('location', 200)->nullable();
            $table->string('sub_type', 200)->nullable();
            $table->string('field_name', 191)->nullable();
            $table->tinyInteger('is_disabled')->default(0);
            $table->string('status', 100)->nullable();
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
