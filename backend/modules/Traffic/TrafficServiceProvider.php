<?php

namespace Modules\Traffic;

use Illuminate\Support\Facades\Event;
use App\Providers\ModuleServiceProvider;
use Modules\BijacInvoice\Models\Bijac;
use Modules\Traffic\Models\Traffic;
use Modules\Traffic\Observers\TrafficObserver;


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

        Traffic::observe(TrafficObserver::class);
    }
}
