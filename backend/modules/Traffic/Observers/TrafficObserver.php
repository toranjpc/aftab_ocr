<?php

namespace Modules\Traffic\Observers;

use Modules\Traffic\Models\Traffic;

class TrafficObserver
{
    const CACHE_TIME = 3600 * 3; // سه ساعت

    public function created(Traffic $item)
    {
        $this->pushToTrafficQueue($item);
    }

    public function updated(Traffic $item)
    {
        $this->replaceInTrafficQueue($item);
    }

    protected function pushToTrafficQueue(Traffic $item, $event = 'Traffic')
    {
        $redis = app('redis');
        $key   = 'Traffic_queue';

        $redis->rpush($key, json_encode([
            'event' => $event,
            'data' => $item->toArray(),
            'gate' => $item->gate_number,
        ]));

        $redis->ltrim($key, -100, -1);
        $redis->expire($key, self::CACHE_TIME);
    }


    protected function replaceInTrafficQueue(Traffic $item)
    {
        $redis = app('redis');
        $key   = 'Traffic_queue';

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
