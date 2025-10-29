<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCcsDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ccs_data', function (Blueprint $table) {
            $table->id();
            $table->string('VehicleNumber', 20)->nullable();
            $table->string('ExitPermissionNumber', 50)->nullable();
            $table->string('ExitPermissionID', 50)->nullable();
            $table->float('Weight')->default(0);
            $table->string('ContainerSize', 10)->nullable();
            $table->boolean('HazardousCode')->default(false);
            $table->string('ReceiptNumber', 50)->nullable();
            $table->dateTime('request_date')->nullable();
            $table->string('vehicle_number_normal', 12)->nullable();
            $table->boolean('revoke_receipt')->default(false);
            $table->string('IsSingleCarry')->nullable();
            $table->string('ContainerNumber')->nullable();
            $table->timestamps();

            $table->unique(['ReceiptNumber', 'VehicleNumber', 'ExitPermissionNumber']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ccs_data');
    }
}
