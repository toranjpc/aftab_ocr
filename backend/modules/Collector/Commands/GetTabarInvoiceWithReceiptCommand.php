<?php

namespace Modules\Collector\Commands;

use Illuminate\Console\Command;
use Modules\Collector\Services\InvoiceService;

class GetTabarInvoiceWithReceiptCommand extends Command
{
    protected $signature = 'get:tabari-with-receipt';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $service = new InvoiceService();
        //
        // BSRCC14030265719
        // BSRCC14030265801
        // BSRCC14030262394
        $data = $service->getWithReceiptNumber('BSRGCBI10316284');

        $service->normalize($data);

        dd($data);
    }
}
