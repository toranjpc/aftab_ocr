<?php

namespace Modules\Traffic\Observers;

use Illuminate\Support\Facades\Cache;
use Modules\Traffic\Models\Traffic;
use Modules\Traffic\Jobs\ProcessTrafficMatch;

class TrafficObserver
{
    const CACHE_TIME = 3600 * 3;
    const CACHE_length = 10;

    public function created(Traffic $item)
    {
        $this->pushToTrafficQueue($item);

        ProcessTrafficMatch::dispatch($item->id);
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
        $all = $redis->lrange($key, 0, -1);
        $filtered = array_filter($all, function ($json) use ($item) {
            $data = json_decode($json, true) ?? [];
            return isset($data['data']['id']) && $data['data']['id'] != $item->id;
        });
        $redis->del($key);
        foreach ($filtered as $row) {
            $redis->rpush($key, $row);
        }
        $this->pushToTrafficQueue($item, 'Traffic_updated');
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
