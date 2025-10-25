<?php

namespace Modules\Traffic\Observers;

use Modules\Traffic\Models\Traffic;
use Modules\Sse\Models\SSE;

class TrafficObserver
{
    public function created(Traffic $item)
    {
        $this->sendSse($item, 'ocr-match');
    }

    public function updated(Traffic $item)
    {
        $this->sendSse($item, 'ocr-match');
    }

    protected function sendSse(Traffic $item, string $event)
    {
        SSE::create([
            'message' => ['data' => $item->toArray()],
            'event' => $event,
            'model' => Traffic::class,
            'receiver_id' => $item->gate_number,
        ]);
    }
}
