<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGcomsReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gcoms_reports', function (Blueprint $table) {
            $table->id();
            // $table->morphs('reportable');
            $table->foreignId('ocr_match_id')->nullable()->constrained();
            // $table->unsignedBigInteger("gcoms_invoice_id")->nullable();
            $table->foreignId('invoice_id')->nullable()->constrained();
            $table->enum("type", ['no_invoice', 'overload']);
            $table->double('overload')->nullable();
            $table->string('status')->default('created');
            $table->string('plate_number');
            $table->enum("plate_type", ['iran', 'afghan', 'other'])->default('iran');
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
        Schema::dropIfExists('gcoms_reports');
    }
}
