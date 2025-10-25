<?php

namespace Modules\Traffic;

use Illuminate\Support\Facades\Event;
use App\Providers\ModuleServiceProvider;
use Modules\BijacInvoice\Models\Bijac;
use Modules\BijacInvoice\Models\TrafficMatch;


class TrafficServiceProvider extends ModuleServiceProvider
{
    function getNamespace()
    {
        return 'Traffic\Controllers';
    }

    function getDir()
    {
        return __DIR__;
    }

    function boot()
    {
        // $this->commands([
        //     PlateMatcher::class,
        // ]);

        TrafficMatch::observe(TrafficObserver::class);
    }
}
