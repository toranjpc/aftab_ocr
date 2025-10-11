<?php

namespace Modules\Ocr;

class TruckBuffer
{
    static function addToBuffer($item)
    {
        $cacheKey = 'match_stack_' . $item->gate_number;

        $stack = cache($cacheKey) ?? [];

        $type = TruckMatcher::getType($item);

        if ($type === false)
            return;


        // if ($type === 'plate')
        //     TruckMatcher::makeSendJobs($item->id);


        $stack[] = ['type' => $type, 'data' => $item];

        cache()->set($cacheKey, $stack);
    }

    static function getBuffer($key)
    {
        $cacheKey = 'match_stack_' . $key;

        return cache($cacheKey) ?? [];
    }

    static function setBuffer($key, $stack)
    {
        $cacheKey = 'match_stack_' . $key;

        cache()->set($cacheKey, $stack);
    }

    static function runBuffer()
    {
        $gate = 1;

        while (1) {
            sleep(0.5);

            $buffer = static::getBuffer($gate);

            if (count($buffer) <= 1)
                continue;

            $item1 = array_shift($buffer);
            $item2 = array_shift($buffer);

            $result = TruckMatcher::plateContainerMatchingWithBuffer($item1, $item2);

            if (gettype($result) === 'array') {
                array_unshift($buffer, $result);
            }

            static::setBuffer($gate, $buffer);
        }
    }
}
