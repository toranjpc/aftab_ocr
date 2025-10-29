<?php

namespace Modules\Collector\Commands;

use Modules\Ocr\Jobs\TruckStatusJob;
use Modules\Ocr\Models\OcrLog;
use Modules\Ocr\TruckMatcher;
use Illuminate\Console\Command;

class ReMatchTrucksCommand extends Command
{
    protected $signature = 'rematch-trucks';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $items = OcrLog::latest()->limit(50)->get();

        foreach ($items as $item) {
            dump('+');
            // TruckMatcher::bijacMatching($item);

            TruckStatusJob::dispatchSync($item->id, 'plate');
            if ($item->container_code)
                TruckStatusJob::dispatchSync($item->id, 'container');
        }
    }

}
