<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrafficTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('traffic', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable(); //

            $table->text('vehicle_image_front_url')->nullable(); //   عکس‌ وسیله نقلیه جلو
            $table->text('vehicle_image_back_url')->nullable(); //   عکس‌ وسیله نقلیه عقب
            $table->text('vehicle_image_left_url')->nullable(); //   عکس‌ وسیله نقلیه چپ
            $table->text('vehicle_image_right_url')->nullable(); //   عکس‌ وسیله نقلیه راست
            $table->text('plate_image_url')->nullable(); //  کراپ شده پلاک 
            $table->text('container_code_image_url')->nullable(); // کراپ شده کانتینر

            $table->enum("direction", ["entry", "exit"])->default("entry"); // ورودی یا خروجی
            $table->timestamp("log_time")->nullable(); // تاریخ زمان
            $table->timestamp("exit_time")->nullable(); // تاریخ زمان
            $table->integer("camera_number")->nullable(); // شماره دوربین
            $table->integer("gate_number")->nullable(); // شماره گیت
            $table->boolean('IMDG')->nullable(); //کالای خطر ناک
            $table->boolean('seal')->nullable(); //پلمپ
            $table->boolean('load_type')->nullable(); //نوع بار

            $table->string("plate_number")->nullable(); // شماره پلاک
            $table->string("plate_number_edit")->nullable(); // شماره پلاک
            $table->string("plate_number_2")->nullable(); // شماره پلاکهای احتمالی
            $table->string("plate_number_by_bijac")->nullable(); // شماره پلاک گرفته شده از بیجک
            $table->string("vehicle_location")->nullable(); // لوکیشن پلاک
            $table->string("plate_type")->nullable(); // نوع پلاک -> ایرانی افغانی اروپایی
            $table->string("vehicle_type")->nullable(); // نوع وسلیه نقلیه -> سواری کامیون

            $table->string("container_code")->nullable(); // کد کانینر
            $table->string("container_code_edit")->nullable(); // کد کانینر
            $table->string("container_code_2")->nullable(); // کد کانینر 2
            $table->string("container_code_3")->nullable(); // کد کانینر 3
            $table->string("container_code_by_bijac")->nullable(); // کد کانینر 3
            // $table->boolean("container_code_validation")->default(0); // تایید اعتبار کد کانیتر (هرسه مثل هم بوده یا ن )
            $table->string("coordinate")->nullable(); // سایز کانینر
            $table->string("container_size")->nullable(); // سایز کانینر
            $table->string("container_type")->nullable(); // نوع بار

            $table->float("ocr_accuracy")->nullable(); // درصد ocr
            $table->float("frequency")->nullable(); // درصد فراوانی
            $table->enum("plate_reading_status", ["read", "confused"])->default("read"); // خوندن یا نخودن

            $table->text("data")->nullable(); // خوندن یا نخودن

            $table->timestamps();
        });
        Schema::create('traffic_bijac_invoice', function (Blueprint $table) {
            $table->id();
            $table->foreignId('traffic_id')->constrained('traffic')->cascadeOnDelete();
            $table->foreignId('bijac_id')->nullable();
            $table->foreignId('invoice_id')->nullable();
            $table->string("type")->nullable();
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
        Schema::dropIfExists('traffic');
        Schema::dropIfExists('traffic_bijac_invoice');
    }
}

/*
$aa = [
    "date" => "2025-10-25",
    "log_time" => "2025-10-25 12:29:22",
    "direction" => "entry",
    "container_code_validation" => "0",
    "camera_number" => "1",
    "gate_number" => "1",

    "container_code" => "CICU_368619_0_45G1",
    "IMDG" => "0",
    "container_code_image" => [],
    "vehicle_image_back" => [],

    "image_time" => "2025-10-25 12:29:21",
    "plate_reading_status" => "read",
    "frequency" => "1.0",
    "ocr_accuracy" => "0.9999951322873434",
];
$aa = [
    "date" => "2025-10-25",
    "log_time" => "2025-10-25 12:29:24",
    "direction" => "entry",
    "container_code_validation" => "0",
    "camera_number" => "1",
    "gate_number" => "1",

    "plate_type" => "iran",
    "plate_number" => "66ein35259",
    "plate_image" => [],
    "vehicle_image_front" => [],

    "image_time" => "2025-10-25 12:29:24",
    "plate_reading_status" => "read",
    "frequency" => "1.0",
    "ocr_accuracy" => "0.9697834253311157",
];
*/