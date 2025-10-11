<?php

namespace Modules\Ocr\MatchingStrategies;

use Modules\Ocr\Models\OcrLog;
use Modules\Ocr\Models\OcrMatch;
use Modules\BijacInvoice\Services\BijacSearchService;

interface MatchStrategyInterface
{
    public function match(OcrLog $ocr, OcrMatch $match, BijacSearchService $bijacService): bool;
}
