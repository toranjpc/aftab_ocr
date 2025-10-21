<?php

namespace Modules\Ocr;

class OcrBuffer
{
    static function addToBuffer($item, $gate, $type = 'plate')
    {
        $cacheKey = $type . '_ocr__stack_' . $gate;
        $stack = cache($cacheKey) ?? [];

        if (count($stack) > 9) {
            array_splice($stack, 9);
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
