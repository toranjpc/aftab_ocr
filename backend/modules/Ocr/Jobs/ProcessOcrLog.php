<?php

namespace Modules\Ocr\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Ocr\Models\OcrMatch;
use Illuminate\Support\Facades\Cache;
use Modules\BijacInvoice\Services\BijacSearchService;
use Modules\Ocr\MatchingStrategies\ContainerMatchStrategy;
use Modules\Ocr\MatchingStrategies\PlateMatchStrategy;
use Modules\Ocr\Models\OcrLog;

class ProcessOcrLog implements ShouldQueue
// class ProcessOcrLog
{
    use Dispatchable, InteractsWithQueue;

    public $tries = 3;

    public $backoff = [10, 30, 60];

    protected $ocrId;

    public function __construct($ocrId)
    {
        $this->ocrId = $ocrId;
    }

    public function handle(BijacSearchService $bijacService)
    {
        $lock = Cache::lock('match_container_plate_lock', 5);
        if (!$lock->get()) {
            return $this->release();
        }

        try {
            $ocr = OcrLog::findOrFail($this->ocrId);
            $limit = config('ocr.last_matches_limit', 10);

            $matches = OcrMatch::where('ocr_log_id', '<', $ocr->id)
                // ->orderBy('created_at', 'desc')
                ->orderBy('ocr_log_id', 'desc')
                ->take($limit)
                ->get();

            $strategies = [
                new PlateMatchStrategy(),
                new ContainerMatchStrategy()
            ];

            foreach ($strategies as $strategy) {
                // try {
                //     if (!empty($ocr->plate_number)) {
                //         log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_" . $ocr->gate_number . ".log"),])
                //             ->info("ProcessOcrLog for plate_number : {$ocr->plate_number}  ");
                //     } elseif (!empty($ocr->container_code)) {
                //         log::build(['driver' => 'single', 'path' => storage_path("logs/gatelog_" . $ocr->gate_number . ".log"),])
                //             ->info("ProcessOcrLog for container_code : {$ocr->container_code}  ");
                //     }
                // } catch (\Throwable $th) {
                //     //throw $th;
                // }
                if ($strategy->match($ocr, $matches, $bijacService)) break;
            }
        } finally {
            $lock->release();
        }
    }
}
