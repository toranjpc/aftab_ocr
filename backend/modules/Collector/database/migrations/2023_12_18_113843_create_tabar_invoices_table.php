<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTabarInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tabar_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('InvoiceID');
            $table->string('TrafficType')->nullable();
            $table->string('InvoiceNumber')->nullable();
            $table->date('InvoiceDate')->nullable();
            $table->date('CalculationDate')->nullable();
            $table->string('ReceiptNumber')->nullable();
            $table->string('Vessel')->nullable();
            $table->string('Voyage')->nullable();
            $table->string('BLNo')->nullable();
            $table->string('Weight')->nullable();
            $table->date('DischargeDate')->nullable();
            $table->string('CustomsDecNumber')->nullable();
            $table->string('CIFValue')->nullable();
            $table->string('StorageDuration')->nullable();
            $table->string('GoodsOwnerName')->nullable();
            $table->string('GoodsOwnerNationalID')->nullable();
            $table->string('GoodsOwnerPostalCode')->nullable();
            $table->string('GoodsOwnerEconommicCode')->nullable();
            $table->string('InsuranceNumber')->nullable();
            $table->date('PaymentDate')->nullable();
            $table->string('PaymentCode')->nullable();
            $table->string('PAN')->nullable();
            $table->string('SystemTraceNumber')->nullable();
            $table->string('PayRequestTraceNo')->nullable();
            $table->string('ParkingCost')->nullable();
            $table->string('Tax3')->nullable();
            $table->string('Tax6')->nullable();
            $table->BigInteger('Total')->nullable();
            $table->string('TraceNo')->nullable();
            $table->unsignedBigInteger('PimacsID')->nullable();
            $table->string('TaxTraceNo')->nullable();
            $table->string('TaxPimacsID')->nullable();
            $table->string('AccountTitle')->nullable();
            $table->string('BankName')->nullable();
            $table->string('BranchName')->nullable();
            $table->string('AccountNumber')->nullable();
            $table->string('ShabaNo')->nullable();
            $table->string('SellerName')->nullable();
            $table->string('SellerNationalID')->nullable();
            $table->string('TaxAccountTitle')->nullable();
            $table->string('TaxAccountShabaNo')->nullable();

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
        Schema::dropIfExists('tabar_invoices');
    }
}
