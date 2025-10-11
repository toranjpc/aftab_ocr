<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMatchStatusToOcrMatchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ocr_matches', function (Blueprint $table) {
            $table->datetime('log_time', 6)->change();
            $table->foreignId('ocr_log_id')->nullable()->after('id')->constrained();
            $table->string("plate_number_3")->nullable()->after('plate_number_2');
            $table->string('match_status')->nullable()->after('data');
            $table->boolean("valid_exit_gcoms")->default(false)->after('data');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ocr_matches', function (Blueprint $table) {
            // $table->dropIndex(['created_at']);
            $table->dropColumn('valid_exit_gcoms');
            $table->dropColumn('match_status');
            $table->dropColumn('plate_number_3');
            $table->dropForeign(['ocr_log_id']);
            $table->dropColumn('ocr_log_id');
            $table->datetime('log_time')->change();
        });
    }
}
