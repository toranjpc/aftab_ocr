<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_invoice_id')->unique();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('invoice_number');
            $table->string('receipt_number');
            $table->dateTime('pay_date');
            $table->string('pay_trace')->nullable();
            $table->unsignedFloat('amount');
            $table->string('weight', 20)->nullable();
            $table->unsignedInteger('number')->nullable();
            $table->unsignedFloat('tax');
            $table->string('kutazh')->nullable();
            $table->dateTime('request_date')->nullable();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `invoices`  MODIFY COLUMN `amount` FLOAT NOT NULL,  MODIFY COLUMN `tax` FLOAT NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
