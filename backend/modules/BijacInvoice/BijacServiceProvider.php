<?php

namespace Modules\BijacInvoice;

use App\Providers\ModuleServiceProvider;
use Modules\BijacInvoice\Console\GetBijacs;

class BijacServiceProvider extends ModuleServiceProvider
{
    function getNamespace()
    {
        return 'Bijac\Controllers';
    }

    function getDir()
    {
        return __DIR__;
    }

    function boot()
    {
        $this->registerSchedule();
    }

    protected function registerSchedule()
    {
        $scheduleFile = $this->getDir() . '/schedule.php';

        if (file_exists($scheduleFile)) {
            $this->app->booted(function () use ($scheduleFile) {
                if (class_exists(\Illuminate\Console\Scheduling\Schedule::class)) {
                    $schedule = app(\Illuminate\Console\Scheduling\Schedule::class);
                    require $scheduleFile;
                }
            });
        }
    }
}
