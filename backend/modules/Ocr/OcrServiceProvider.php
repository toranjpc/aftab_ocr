<?php

namespace Modules\Ocr;

use Modules\Ocr\Models\OcrMatch;
use Modules\Ocr\Models\OcrLog;
use Modules\Ocr\Console\PlateMatcher;
use Illuminate\Support\Facades\Event;
use App\Providers\ModuleServiceProvider;
use Modules\Ocr\Jobs\TruckStatusJob;
use Modules\Ocr\BijacMatcher;
use Modules\Ocr\Observers\OcrMatchObserver;
use Modules\BijacInvoice\Models\Bijac;


class OcrServiceProvider extends ModuleServiceProvider
{
    function getNamespace()
    {
        return 'Ocr\Controllers';
    }

    function getDir()
    {
        return __DIR__;
    }

    function boot()
    {
        $this->commands([
            PlateMatcher::class,
        ]);

        //$this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Event::listen('eloquent.created: ' . OcrLog::class, function ($item) {
        //     TruckBuffer::addToBuffer($item);
        // });

        OcrMatch::observe(OcrMatchObserver::class);

        // Event::listen('eloquent.created: ' . OcrMatch::class, function ($item) {
        //     BijacMatcher::bijacMatching($item, true);
        //     TruckStatusJob::dispatch($item->id, 'plate');
        //     if (!!$item->container_code_image_url) {
        //         TruckStatusJob::dispatch($item->id, 'container');
        //     }
        // });

        // Event::listen('eloquent.updated: ' . OcrMatch::class, function ($item) {

        //     BijacMatcher::bijacMatching($item);
        //     TruckStatusJob::dispatch($item->id, 'plate');
        //     if (!!$item->container_code_image_url) {
        //         TruckStatusJob::dispatch($item->id, 'container');
        //     }
        // });

    }
}
