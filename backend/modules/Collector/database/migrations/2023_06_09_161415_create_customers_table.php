<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('shenase_meli')->unique();
            $table->string('title')->nullable();
            $table->date('registrationDate')->nullable();
            $table->string('status')->nullable();
            $table->string('registrationTypeTitle')->nullable();
            $table->date('lastCompanyNewsDate')->nullable();
            $table->string('shomare_sabt')->nullable();
            $table->string('code_eghtesadi')->nullable();
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('webSite')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->text('socialMedias')->nullable();
            $table->enum('type', ['private', 'juridical'])->default('private'); // حقیقی یا حقوقی
            $table->boolean('is_safe')->default(true); // آیا اطلاعات مشتری قابل اعتماد هست
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('customers');
    }
}
