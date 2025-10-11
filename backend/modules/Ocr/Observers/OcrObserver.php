<?php

namespace Modules\Ocr\Observers;

use Modules\Ocr\Models\OcrLog;
// use Modules\Ocr\BijacMatcher;//بررسی شود
use Modules\Ocr\TruckMatcher;
use Modules\Ocr\Jobs\TruckStatusJob;

class OcrObserver
{
    public function created(OcrLog $item)
    {

        // BijacMatcher::bijacMatching($item, true);//بررسی شود
        TruckMatcher::bijacMatching($item, true);
        // TruckStatusJob::dispatch($item->id, 'plate');
        // if (!!$item->container_code_image_url) {
        //     TruckStatusJob::dispatch($item->id, 'container');
        // }
    }

    public function updated(OcrLog $item)
    {
        // BijacMatcher::bijacMatching($item);//بررسی شود
        TruckMatcher::bijacMatching($item);
        // TruckStatusJob::dispatch($item->id, 'plate');
        // if (!!$item->container_code_image_url) {
        //     TruckStatusJob::dispatch($item->id, 'container');
        // }
    }
}
