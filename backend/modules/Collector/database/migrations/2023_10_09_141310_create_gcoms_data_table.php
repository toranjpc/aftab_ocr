<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGcomsDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gcoms_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();

            $table->string('BLHeadNumber')->nullable();
            $table->string('BillingType')->nullable();
            $table->text('BuyerAddress')->nullable();
            $table->string('BuyerEconomicCode')->nullable();
            $table->string('BuyerName')->nullable();
            $table->string('BuyerNationalID')->nullable();
            $table->string('BuyerPhone')->nullable();
            $table->string('BuyerPostalCode')->nullable();
            $table->string('BuyerRegNo')->nullable();
            $table->text('CommodityName')->nullable();
            $table->string('CustomNb')->nullable();
            $table->string('CustomValue')->nullable();
            $table->timestamp('FinalDate')->nullable();
            $table->string('FinalDue')->nullable();
            $table->date('FirstEntranceDate')->nullable(); // todo: cast to gorgion
            $table->string('GrossWeight')->nullable();
            $table->string('Heavy')->nullable();
            $table->string('InsuranceNumber')->nullable();
            $table->unsignedFloat('InvoiceDue', 20);
            $table->string('InvoiceHeadStatusName')->nullable();
            $table->timestamp('IssueDate')->nullable();
            $table->string('NonPalletized')->nullable();
            $table->string('OperatorName')->nullable();
            $table->string('OperatorSerial')->nullable();
            $table->string('PackNB')->nullable();
            $table->string('PackageName')->nullable();
            $table->string('ParentServiceName')->nullable();
            $table->text('SellerAddress')->nullable();
            $table->string('SellerEconomicCode')->nullable();
            $table->text('SellerName')->nullable();
            $table->string('SellerNationalID')->nullable();
            $table->string('SellerPhone')->nullable();
            $table->string('SellerPostalCode')->nullable();
            $table->string('SellerRegNo')->nullable();
            $table->string('ServiceName')->nullable();
            $table->string('StoreName')->nullable();
            $table->string('StoreReceiptHeadTypeName')->nullable();
            $table->string('StoreReceiptItem')->nullable();
            $table->string('StoreReceiptSerial')->nullable();
            $table->string('TariffCategoryName')->nullable();
            $table->string('TrafficName')->nullable();
            $table->string('Travel')->nullable();
            $table->string('TravelDesc')->nullable();
            $table->string('VersionType')->nullable();
            $table->string('VesselName')->nullable();
            $table->string('Volume')->nullable();
            $table->string('Voluminous')->nullable();
            $table->string('duration')->nullable();
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
        Schema::dropIfExists('gcoms_data');
    }
}
