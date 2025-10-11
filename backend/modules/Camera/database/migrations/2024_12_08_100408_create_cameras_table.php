<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCamerasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cameras', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->nullable();
            $table->string('group')->nullable();
            $table->text('stream')->nullable();
            $table->string('ip')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('description')->nullable();
            $table->boolean('active')->default(true);
            $table->string('main_stream')->nullable();
            $table->string('camera_brand')->nullable();
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
        Schema::dropIfExists('cameras');
    }
}
