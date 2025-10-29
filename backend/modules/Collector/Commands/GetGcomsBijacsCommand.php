<?php

namespace Modules\Collector\Commands;

use Illuminate\Console\Command;
use Modules\Collector\Services\GcomsService;

class GetGcomsBijacsCommand extends Command
{
    protected $signature = 'get:gcoms-bijacs';

    protected $description = 'get gcoms bijacs data';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $lastDate = cache('gcoms_bijac_date');

        $lastDate = $lastDate ? (new \Carbon\Carbon($lastDate)) : now()->subDays(2);

        dump([
            verta($lastDate),
        ]);

        if (now()->lessThan($lastDate)) {
            cache(['gcoms_bijac_date' => $lastDate->subDays(3)]);
            return;
        }

        GcomsService::getBijacByDate($lastDate);

        $lastDate = $lastDate->addDay();

        cache(['gcoms_bijac_date' => $lastDate->timestamp]);
    }
}
