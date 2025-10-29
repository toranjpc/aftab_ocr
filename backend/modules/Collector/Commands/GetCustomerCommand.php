<?php

namespace Modules\Collector\Commands;

use Modules\Collector\Models\TabarInvoice;
use Modules\Collector\Models\Customer;
use Modules\Gcoms\Models\GcomsData;
use Illuminate\Console\Command;

class GetCustomerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:customer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'get customer';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */


    public function handle()
    {
        dump('start');

        $lastId = cache('lastCustomerId',1);
        $lastId_gcoms = cache('lastGcomsIdget',1);


        $endId = TabarInvoice::max('id');
        $endId_gcoms = GcomsData::max('id');
        while (true) {
            if($endId <= $lastId){
                dump($lastId,"smile it's the end of tabari :)",$endId);
                break;
            }

            $TabarInvoice = TabarInvoice::whereBetween('id', [$lastId, $lastId+1000])->get();
            foreach ($TabarInvoice as $invoice) {
                $check_customer = Customer::where('code_eghtesadi', $invoice->GoodsOwnerEconommicCode)->first();
                if(!$check_customer ){
                Customer::create([
                    'phone' => 'TabarInvoice',
                    'name' => $invoice->GoodsOwnerName,
                    'code_eghtesadi' => $invoice->GoodsOwnerEconommicCode,
                    'shenase_meli' => $invoice->GoodsOwnerNationalID,
                    'postal_code' =>$invoice->GoodsOwnerPostalCode,
                    'type' => strlen($invoice->GoodsOwnerEconommicCode) === 12 ? 'juridical': 'private'
                ]);
            }
            }
            $lastId = $lastId+1000;
            cache(['lastCustomerId' => $lastId]);
            dump($lastId, 'tabari');

        }
        while (true) {
            if($endId_gcoms <= $lastId_gcoms){
                dump($lastId_gcoms,"smile it's the end of gcoms :)",$endId_gcoms);
                break;
            }

            $GcomsData = GcomsData::whereBetween('id', [$lastId_gcoms, $lastId_gcoms+1000])->get();
            foreach ($GcomsData as $data) {
                $check_customer = Customer::where('code_eghtesadi', $data->SellerEconomicCode)->first();
                if(!$check_customer ){
                Customer::create([
                    'phone' => $data->SellerPhone,
                    'name' => $data->SellerName,
                    'code_eghtesadi' => $data->SellerEconomicCode,
                    'shenase_meli' => $data->SellerNationalID,
                    'postal_code' =>$data->SellerPostalCode,
                    'address' => $data->SellerAddress,
                    'shomare_sabt'=>  $data->SellerRegNo,
                    'type' => strlen($data->SellerEconomicCode) === 12 ? 'juridical': 'private'
                ]);

            }
            elseif($check_customer->phone === 'TabarInvoice'){
                $check_customer->address = $data->SellerAddress;
                $check_customer->phone = $data->SellerPhone;
                $check_customer->name = $data->SellerName;
                $check_customer->postal_code =  $data->SellerPostalCode;
                $check_customer->shomare_sabt =  $data->SellerRegNo;
                $check_customer->shenase_meli =  $data->SellerNationalID;
                if(strlen($data->SellerEconomicCode) ===12){
                    if(strlen($check_customer->code_eghtesadi) ===12){
                        dump('ckeck id',$check_customer->id,  ' information');
                    }
                    else{
                        $check_customer->code_eghtesadi = $data->SellerAddress;
                        $check_customer->type = 'juridical';
                    }
                }
                $check_customer->update();

            }
            }
            $lastId_gcoms = $lastId_gcoms+1000;
            cache(['lastGcomsIdget' => $lastId_gcoms]);
            dump($lastId_gcoms,'gcoms');

        }
    }






}
