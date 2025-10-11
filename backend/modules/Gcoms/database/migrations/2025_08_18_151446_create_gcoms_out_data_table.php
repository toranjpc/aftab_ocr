<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGcomsOutDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gcoms_out_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id");
            // $table->unsignedBigInteger("gcoms_invoice_id")->nullable();
            $table->foreignId('invoice_id')->nullable()->constrained();
            $table->string('customNb')->nullable();
            $table->string("plate_number")->nullable();
            $table->double('weight')->nullable();
            $table->date("full_scale_date")->nullable();
            $table->enum("type", ['excel', 'app'])->default('app');
            $table->enum("plate_type", ['iran', 'afghan', 'other'])->default('iran');
            $table->string("gate")->default('0');
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
        Schema::dropIfExists('gcoms_out_data');
    }
}
