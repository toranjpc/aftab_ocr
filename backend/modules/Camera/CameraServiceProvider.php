<?php

namespace Modules\Camera;

use App\Providers\ModuleServiceProvider;


class CameraServiceProvider extends ModuleServiceProvider
{
    function getNamespace()
    {
        return 'Camera\Controllers';
    }

    function getDir()
    {
        return __DIR__;
    }

    function boot()
    {

    }
}
