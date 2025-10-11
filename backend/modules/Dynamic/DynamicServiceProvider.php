<?php

namespace Modules\Dynamic;

use App\Providers\ModuleServiceProvider;

class DynamicServiceProvider extends ModuleServiceProvider
{
    function getNamespace()
    {
        return 'Dynamic\Http\Controllers';
    }

    function getDir()
    {
        return __DIR__;
    }
}
