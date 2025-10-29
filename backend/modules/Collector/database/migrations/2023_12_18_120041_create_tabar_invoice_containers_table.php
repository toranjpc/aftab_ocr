<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTabarInvoiceContainersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tabar_invoice_containers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tabar_invoice_id');
            $table->unsignedBigInteger('wareHouseReceiptID')->nullable();
            $table->string('ContainerNo')->nullable();
            $table->string('ContainerSize')->nullable();
            $table->string('ContainerType')->nullable();
            $table->string('NetWeight')->nullable();
            $table->string('TareWeight')->nullable();
            $table->string('FCLLCL')->nullable();
            $table->string('LoadTypeDes')->nullable();
            $table->unsignedBigInteger('TerminalID')->nullable();
            $table->string('Warehousing')->nullable();
            $table->string('Yard')->nullable();
            $table->timestamp('ExitDate')->nullable();
            $table->timestamps();

            $table->unique(['ContainerNo', 'tabar_invoice_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tabar_invoice_containers');
    }
}
