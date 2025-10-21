<?php

namespace Modules\BijacInvoice\Services;

use Modules\BijacInvoice\Models\Bijac;

class BijacSearchService
{
    const MATCH_DAY = 30;

    public static function getBijacs($item, $isEdited = false)
    {
        if (
            (!!$item->container_code && !$isEdited) ||
            (!!$item->container_code_edit && $isEdited)
        )
            return static::getContainerBijacs($item, $isEdited);

        return static::getPlateBijacs($item, $isEdited);
    }

    public static function extractDigits($string)
    {
        preg_match_all('/\d+/', $string, $matches);

        return implode('', $matches[0]);
    }


    public static function getPlateBijacs($item, $isEdited = false)
    {
        return Bijac::forPlate($item, $isEdited)->get();
    }

    public static function getContainerBijacs($item, $isEdited = false)
    {
        return Bijac::forContainer($item, $isEdited);
    }
}
