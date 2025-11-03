<?php

namespace Modules\Traffic\Observers;

use Illuminate\Support\Facades\Cache;
use Modules\Traffic\Models\Traffic;
use Modules\Traffic\Jobs\ProcessTrafficMatch;
use Illuminate\Support\Facades\Log;
use Modules\Sse\Models\SSE;

class TrafficObserver
{
    const CACHE_TIME = 3600 * 3;
    const CACHE_length = 10;

    public function created(Traffic $item)
    {
        $this->pushToTrafficQueue($item);

        ProcessTrafficMatch::dispatch($item->id);

        $this->createSSE($item);
    }

    public function updated(Traffic $item)
    {
        $this->replaceInTrafficQueue($item);

        if (
            $item->isDirty() &&
            !($item->isDirty('plate_number_by_bijac') || $item->isDirty('container_code_by_bijac'))
        ) {
            ProcessTrafficMatch::dispatch($item->id);
        }

        $this->createSSE($item);
    }

    protected function pushToTrafficQueue(Traffic $item, $event = 'Traffic')
    {
        $redis = app('redis');
        $key   = 'Traffic_queue_' . $item->gate_number;
        $redis->rpush($key, json_encode([
            'event' => $event,
            'data' => $item->toArray(),
        ]));
        $redis->ltrim($key, -self::CACHE_length, -1);
        $redis->expire($key, self::CACHE_TIME);
    }


    protected function replaceInTrafficQueue(Traffic $item)
    {
        $redis = app('redis');
        $key   = 'Traffic_queue_' . $item->gate_number;
        $all   = collect($redis->lrange($key, 0, -1));

        foreach ($all as $index => $json) {
            $data = json_decode($json, true);
            if (($data['data']['id'] ?? null) == $item->id) {
                $redis->lset($key, $index, '__TO_DELETE__');
                $redis->lrem($key, 1, '__TO_DELETE__');
                break;
            }
        }

        $this->pushToTrafficQueue($item, 'Traffic_updated');
    }

    public function createSSE(Traffic $item)
    {
        SSE::create([
            // 'message' => ['data' => $item->toArray()],
            'message' => ['data' => $item->id],
            'event' => 'Traffic',
            'model' => Traffic::class,
            'receiver_id' => $item->gate_number,
        ]);
    }


    /*
    // @todo: move to SSEController integration later
    protected function sendSse(Traffic $item, string $event)
    {
        SSE::create([
            'message' => ['data' => $item->toArray()],
            'event' => $event,
            'model' => Traffic::class,
            'receiver_id' => $item->gate_number,
        ]);
    }
    */
}
