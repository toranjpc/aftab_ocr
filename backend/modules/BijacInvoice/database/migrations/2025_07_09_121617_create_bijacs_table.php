<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBijacsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bijacs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_bijac_id')->unique();
            $table->string('plate', 50)->nullable();
            $table->string('plate_normal', 50)->nullable();
            $table->string('dangerous_code', 5)->nullable();
            $table->string('receipt_number', 50)->nullable();
            $table->string('gross_weight', 50)->nullable();
            $table->unsignedInteger('pack_number')->nullable();
            $table->boolean('is_single_carry')->nullable();
            $table->string('container_size', 10)->nullable();
            $table->string('container_number', 50)->nullable();
            $table->dateTime('bijac_date')->nullable();
            $table->string('bijac_number')->nullable();
            $table->string('vehicles_type')->nullable();
            $table->string('exit_permission_iD', 50)->nullable();
            $table->enum('type', ['gcoms', 'ccs','aftab'])->default('ccs');
            // $table->boolean('revoke_receipt')->default(false);
            $table->tinyInteger('revoke_number')->default(0);
            $table->timestamps();

            $table->index('receipt_number');

            $table->index(['plate_normal', 'bijac_date'], 'idx_bijac_date_palte_normal');
            $table->index([ 'bijac_date','plate'], 'idx_bijac_date_plate');

            $table->index(['container_number', 'bijac_date'], 'idx_bijac_date_container_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bijacs');
    }
}
