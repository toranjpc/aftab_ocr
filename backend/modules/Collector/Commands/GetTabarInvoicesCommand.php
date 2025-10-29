<?php

namespace Modules\Collector\Commands;

use Illuminate\Console\Command;
use Modules\Collector\Services\InvoiceService;

class GetTabarInvoicesCommand extends Command
{
    protected $signature = 'get:tabar-invoice-pro';

    protected $description = 'get tabar invoice';

    protected $itemPerRequest = 100;

    public function __construct()
    {
        parent::__construct();
    }

    protected $time = 0;

    function getCache()
    {
        $lastId = 0;
        $date = cache('lastTabariDate');
        $date = $date ? (new \Carbon\Carbon($date)) : now()->subDays(25);

        return [$lastId, $date];
    }

    function getLastId($res)
    {
        $lastInvoice = collect($res['Invoices'] ?? [])->sortBy('InvoiceID')->last();

        return $lastInvoice['InvoiceID'];
    }

    function checkTime()
    {
        $this->time++;

        if ($this->time >= 200)
            dd('timeout');
    }

    public function handle()
    {
        dump('*******start*******');

        $this->time = 0;

        $service = new InvoiceService($this->itemPerRequest);

        [$lastId, $date] = $this->getCache();

        dump($date->toDateTimeString());

        if (now()->addDay()->lessThan($date)) {
            $date->subDays(3);
            cache(['lastTabariDate' => $date->timestamp]);
            return;
        }

        while (true) {
            $this->checkTime();

            $res = $service->getByDate($date, $lastId);
			
			if(isset($res['TotalRowsCount'])) {

				if ($res['TotalRowsCount'] > 0) {
					$lastId = $this->getLastId($res) ?? $lastId;
					$service->normalize($res);
				}

				if ($res['TotalRowsCount'] === 0) {
					$date->addDay();
					cache(['lastTabarId' => $lastId]);
					cache(['lastTabariDate' => $date->timestamp]);
					dump('*******done*******');
					break;
				}
			}
        }

        dump('*******done*******');

        $this->handle();
    }

}
