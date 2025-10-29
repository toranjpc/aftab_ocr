<?php

namespace Modules\Traffic;

use Illuminate\Support\Facades\Event;
use App\Providers\ModuleServiceProvider;
use Modules\BijacInvoice\Models\Bijac;
// use Modules\BijacInvoice\Models\Invoice;
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


        Bijac::resolveRelationUsing('Traffics', function ($bijac) {
            return $bijac->belongsToMany(
                Traffic::class,
                'traffic_bijac_invoice',
                'bijac_id',
                'traffic_id'
            )
                ->withTimestamps();
        });
        Bijac::resolveRelationUsing('PlateTraffics', function ($bijac) {
            return $bijac->belongsToMany(
                Traffic::class,
                'traffic_bijac_invoice',
                'bijac_id',
                'traffic_id'
            )
                ->withPivot('type')
                ->wherePivot('type', 'Plate')
                ->withTimestamps();
        });
        Bijac::resolveRelationUsing('ContainerTraffics', function ($bijac) {
            return $bijac->belongsToMany(
                Traffic::class,
                'traffic_bijac_invoice',
                'bijac_id',
                'traffic_id'
            )
                ->withPivot('type')
                ->wherePivot('type', 'Container')
                ->withTimestamps();
        });


        Traffic::observe(TrafficObserver::class);
    }
}
