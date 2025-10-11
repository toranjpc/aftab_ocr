<?php

namespace Modules\Ocr\Observers;

use Modules\Ocr\Models\OcrMatch;
use Modules\Ocr\BijacMatcher;
use Modules\Ocr\Jobs\EditedMatchBijacs;
use Modules\Ocr\Jobs\TruckStatusJob;

class OcrMatchObserver
{
    public function created(OcrMatch $item)
    {
        // BijacMatcher::bijacMatching($item, true);
        TruckStatusJob::dispatch($item->id, 'plate');
        // if (!!$item->container_code_image_url) {
        //     TruckStatusJob::dispatch($item->id, 'container');
        // }
    }

    public function updated(OcrMatch $item)
    {
        if($item->isDirty('plate_number_edit') || $item->isDirty('container_code_edit')) {
            EditedMatchBijacs::dispatch($item->id);
        }

        // BijacMatcher::bijacMatching($item);
        if ($item->isDirty() && !$item->isDirty('match_status')) {
            TruckStatusJob::dispatch($item->id, 'plate');
            // if (!!$item->container_code_image_url) {
            //     TruckStatusJob::dispatch($item->id, 'container');
            // }
        }
    }
}
