<?php

namespace Modules\Traffic\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Modules\Traffic\Models\Traffic;
use Modules\Traffic\Strategy\PlateMatchStrategy;
use Modules\Traffic\Strategy\ContainerMatchStrategy;

class ProcessTrafficMatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue;

    public $tries = 3;
    public $backoff = [10, 30, 60];
    protected $TrafficId;

    public function __construct($TrafficId)
    {
        $this->TrafficId = $TrafficId;
    }

    public function handle() //BijacSearchService $bijacService
    {
        $lock = Cache::lock('match_container_plate_lock', 15);
        if (!$lock->get()) {
            return $this->release();
        }

        try {
            $Traffic = Traffic::select("id")->findOrFail($this->TrafficId);
            $limit = config('ocr.last_matches_limit');

            $matches = Traffic::orderBy('id', 'desc')
                // ->orderBy('created_at', 'desc')
                ->where('id', '<', $Traffic->id)
                ->take($limit)
                ->get();

            $strategies = [
                new PlateMatchStrategy(),
                new ContainerMatchStrategy()
            ];

            foreach ($strategies as $strategy) {
                if ($strategy->match($Traffic, $matches)) break;
            }
        } finally {
            $lock->release();
        }
    }
}
