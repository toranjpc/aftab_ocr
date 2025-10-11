<?php

namespace Modules\Sse;

use App\Providers\ModuleServiceProvider;
use Modules\Station\Models\StationGate;
use Illuminate\Support\Facades\Event;
use Modules\Ocr\Models\TruckLog;
use Modules\Ocr\Models\OcrLog;
use Modules\Ocr\Models\OcrMatch;
use Modules\Sse\Models\SSE;

class SseServiceProvider extends ModuleServiceProvider
{
    function getNamespace()
    {
        return 'Sse\Controllers';
    }

    function getDir()
    {
        return __DIR__;
    }

    function boot()
    {
        Event::listen('eloquent.created: ' . OcrLog::class, function ($item) {
            SSE::create([
                // 'message' => ['data' => $item->toArray()],
                'message' => ['data' => $item->id],
                'event' => 'ocr-log',
                'model' => OcrLog::class,
                'receiver_id' => $item->gate_number,
            ]);
        });

        Event::listen('eloquent.created: ' . OcrMatch::class, function ($item) {
            SSE::create([
                // 'message' => ['data' => $item->toArray()],
                'message' => ['data' => $item->id],
                'event' => 'ocr-match',
                'model' => OcrMatch::class,
                'receiver_id' => $item->gate_number,
            ]);
        });

        Event::listen('eloquent.updated: ' . OcrMatch::class, function ($item) {
            SSE::create([
                // 'message' => ['data' => $item->toArray()],
                'message' => ['data' => $item->id],
                'event' => 'ocr-match',
                'model' => OcrMatch::class,
                'receiver_id' => $item->gate_number,
            ]);
        });
    }
}
