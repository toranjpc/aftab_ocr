<?php

namespace Modules\Collector\Commands;

use Illuminate\Console\Command;
use Modules\Collector\Services\GcomsService;
use Modules\Gcoms\Models\GcomsInvoice;

class GetGcomsCommand extends Command
{
    protected $signature = 'get:gcoms';

    protected $description = 'get gcoms data';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $invoices = GcomsInvoice::whereNotNull('sazman_invoice_number')
            ->whereNotNull('receipt_number')
            ->whereDoesntHave('gcomsData')
            ->where('created_at', '<=', now()->subDays(3))
            ->where('try_get_data', '<', 2)
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();
        foreach ($invoices as $invoice) {
            $year = (string) verta($invoice->sazman_date)->year;

            $year = substr($year, 2, 2);

            $invoiceSerial = 'AFTAB' . $year . '-' . $invoice->sazman_invoice_number;
            echo $invoiceSerial;
            echo "\n";
            echo $invoice->receipt_number;
          	echo "\n";
           $dd= GcomsService::getInvoice($invoiceSerial, $invoice->receipt_number);
           $invoice->increment('try_get_data');
           print_r($dd);
          	echo "\n";
        }
    }
}
