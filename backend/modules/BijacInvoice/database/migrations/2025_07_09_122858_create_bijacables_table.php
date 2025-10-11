<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBijacablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bijacables', function (Blueprint $table) {
            $table->unsignedBigInteger('bijac_id');
            $table->morphs('bijacable');
            $table->primary(['bijac_id', 'bijacable_id', 'bijacable_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bijacables');
    }
}
