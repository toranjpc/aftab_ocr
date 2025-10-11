<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOcrMatchesTable extends Migration
{
    public function up()
    {
        Schema::create('ocr_matches', function (Blueprint $table) {
            $table->id();

            $table->text('vehicle_image_front_url')->nullable(); //   عکس‌ وسیله نقلیه جلو
            $table->text('vehicle_image_back_url')->nullable(); //   عکس‌ وسیله نقلیه عقب
            $table->text('vehicle_image_left_url')->nullable(); //   عکس‌ وسیله نقلیه چپ
            $table->text('vehicle_image_right_url')->nullable(); //   عکس‌ وسیله نقلیه راست

            $table->text('plate_image_url')->nullable(); //  عکس‌ خود آبجکت‌ها ( پلاک یا کد کانتینر)
            $table->string("plate_type")->nullable(); // نوع پلاک -> ایرانی افغانی اروپایی
            $table->string("plate_number")->nullable(); // شماره پلاک
            $table->string("plate_number_2")->nullable(); // شماره پلاکهای احتمالی
            $table->string("plate_number_edit")->nullable(); // شماره پلاک

            $table->string("vehicle_type")->nullable(); // نوع وسلیه نقلیه -> سواری کامیون

            $table->integer("camera_number")->nullable(); // شماره دوربین
            $table->integer("gate_number")->nullable(); // شماره گیت

            $table->timestamp("log_time")->nullable(); // تاریخ زمان
            $table->timestamp("exit_time")->nullable(); // تاریخ زمان

            $table->text('container_code_image_url')->nullable(); //  عکس‌ خود آبجکت‌ها ( پلاک یا کد کانتینر)
            $table->string("container_size")->nullable(); // سایز کانینر
            $table->string("container_type")->nullable(); // نوع بار
            $table->string("container_code")->nullable(); // کد کانینر
            $table->string("container_code_edit")->nullable(); // کد کانینر
            $table->string("container_code_2")->nullable(); // کد کانینر 2
            $table->string("container_code_3")->nullable(); // کد کانینر 3
            $table->boolean("container_code_validation")->default(0); // تایید اعتبار کد کانیتر (هرسه مثل هم بوده یا ن )

            $table->boolean('IMDG')->nullable(); //کالای خطر ناک
            $table->boolean('seal')->nullable(); //پلمپ

            $table->longText("data")->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ocr_matches');
    }
}
