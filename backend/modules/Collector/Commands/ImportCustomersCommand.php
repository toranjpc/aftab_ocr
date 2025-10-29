<?php

namespace Modules\Collector\Commands;

use Modules\Collector\Services\CustomerService;
use Modules\Collector\Models\Customer;
use Modules\Gcoms\Models\GcomsData;
use Illuminate\Console\Command;

class ImportCustomersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:customers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        $allGcoms = GcomsData::all();
        foreach ($allGcoms as $item) {
            $service = new CustomerService();

            $customer = $service->import($item->toArray());

            if (gettype($customer) === 'array') {
                $customer = Customer::where('shenase_meli', $customer['shenase_meli'])->first();
            }

            $item->customer_id = $customer->id;

            $item->save();
        }

        // $allTabars = TabarInvoice::all();
        // foreach ($allTabars as $item) {
        //     $service = new CustomerService();

        //     $customer = $service->import($item->toArray());

        //     if (gettype($customer) === 'array') {
        //         $customer = Customer::where('shenase_meli', $customer['shenase_meli'])->first();
        //     }

        //     $item->customer_id = $customer->id;

        //     $item->save();
        // }
    }
}
