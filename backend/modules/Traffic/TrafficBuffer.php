<?php

namespace Modules\Traffic;

class TrafficBuffer
{
    static function addToBuffer($item, $gate, $type = 'plate', $stackCount = 10)
    {
        $cacheKey = $type . '_ocr__stack_' . $gate;
        $stack = cache($cacheKey) ?? [];

        if (count($stack) >= $stackCount) {
            array_splice($stack, $stackCount);
        }

        array_unshift($stack, $item);
        cache()->set($cacheKey, $stack);
    }

    static function getBuffer($gate, $type = 'plate')
    {
        $cacheKey = $type . '_ocr__stack_' . $gate;

        return cache($cacheKey) ?? [];
    }
}
