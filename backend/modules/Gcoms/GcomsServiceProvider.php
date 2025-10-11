<?php

namespace Modules\Gcoms;

use App\Providers\ModuleServiceProvider;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Gcoms\Models\GcomsOutNotPermission;
use Modules\Ocr\Models\OcrLog;

class GcomsServiceProvider extends ModuleServiceProvider
{
    function getNamespace()
    {
        return 'Gcoms\Controllers';
    }

    function getDir()
    {
        return __DIR__;
    }

    function boot()
    {
        // OcrLog::resolveRelationUsing('gcomsOutNotPermission', function ($orderModel): HasOne {
        //     return $orderModel->hasOne(GcomsOutNotPermission::class);
        // });
    }
}
