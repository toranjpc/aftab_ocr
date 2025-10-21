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
        $lock = Cache::lock('match_container_plate_lock', 15);

        if (!$lock->get()) {
            return $this->release();
        }

        try {
            $ocr = OcrLog::findOrFail($this->ocrId);
            $limit = config('ocr.last_matches_limit');

            $matches = OcrMatch::where('ocr_log_id', '<', $ocr->id)
                // ->orderBy('created_at', 'desc')
                ->orderBy('ocr_log_id', 'desc')
                ->take($limit)
                ->get();

            $strategies = [
                new ContainerMatchStrategy(),
                new PlateMatchStrategy()
            ];

            foreach ($strategies as $strategy) {
                if ($strategy->match($ocr, $matches, $bijacService)) break;
            }
        } finally {
            $lock->release();
        }
    }
}
