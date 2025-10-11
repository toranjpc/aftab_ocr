<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTruckLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('truck_logs', function (Blueprint $table) {
            $table->id();
            $table->text('vehicle_image_front_url')->nullable(); //   عکس‌ وسیله نقلیه جلو
            $table->text('vehicle_image_back_url')->nullable(); //   عکس‌ وسیله نقلیه عقب
            $table->text('vehicle_image_left_url')->nullable(); //   عکس‌ وسیله نقلیه چپ
            $table->text('plate_image_url')->nullable(); //  عکس‌ خود آبجکت‌ها ( پلاک یا کد کانتینر)
            $table->string("plate_type")->nullable(); // نوع پلاک -> ایرانی افغانی اروپایی
            $table->timestamp("log_time")->nullable(); // تاریخ زمان
            $table->integer("camera_number")->nullable(); // شماره دوربین
            $table->integer("gate_number")->nullable(); // شماره گیت
            $table->string("plate_number")->nullable(); // شماره پلاک
            $table->float("ocr_accuracy")->nullable(); // درصد ocr

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
        Schema::dropIfExists('truck_logs');
    }
}
