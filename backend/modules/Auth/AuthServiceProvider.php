<?php

namespace Modules\Auth;

use App\Providers\ModuleServiceProvider;

class AuthServiceProvider extends ModuleServiceProvider
{
    function getNamespace()
    {
        return 'Auth\Http\Controllers';
    }

    function getDir()
    {
        return __DIR__;
    }
}
