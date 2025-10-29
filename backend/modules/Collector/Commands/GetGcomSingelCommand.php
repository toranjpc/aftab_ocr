<?php

namespace Modules\Collector\Commands;

use Illuminate\Console\Command;
use Modules\Collector\Services\GcomsService;
use Modules\Gcoms\Models\GcomsInvoice;

class GetGcomSingelCommand extends Command
{
    protected $signature = 'get:gcoms-s';

    protected $description = 'get gcoms data';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {

        // receipt_number  شماره قبض انبار
        // invoiceSerial شماره فاکتور سازمان
        $invoiceSerial = "AFTAB03-5312";
        $receipt_number = "10315775";
      $data =  GcomsService::getInvoice($invoiceSerial, $receipt_number);
      dd($data);
    }
}
