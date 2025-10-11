<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPlateNumber2ToOcrLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ocr_logs', function (Blueprint $table) {
            $table->datetime('log_time', 6)->change();
            $table->string('plate_number_2')->nullable()->after('plate_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ocr_logs', function (Blueprint $table) {
            $table->dropColumn('plate_number_2');
            $table->datetime('log_time')->change();
        });
    }
}
