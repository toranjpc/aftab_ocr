<?php

namespace Modules\Collector;

use Modules\Collector\Models\CcsData;
use App\Providers\ModuleServiceProvider;
use Modules\Collector\Services\PlateService;
use Modules\Collector\Commands\GetCcsCommand;
use Modules\Collector\Commands\GetGcomsCommand;
use Modules\Collector\Commands\GetCustomerCommand;
use Modules\Collector\Commands\GetCcsByCargoCommand;
use Modules\Collector\Commands\ReMatchTrucksCommand;
use Modules\Collector\Commands\GetGcomsBijacsCommand;
use Modules\Collector\Commands\FindDiscrepancyCommand;
use Modules\Collector\Commands\ImportCustomersCommand;
use Modules\Collector\Commands\GetTabarInvoicesCommand;
use Modules\Collector\Commands\InvoiceBandarImportCommand;
use Modules\Collector\Commands\GetTabarInvoicesByCcsCommand;
use Modules\Collector\Commands\GetTabarInvoiceWithReceiptCommand;
use Modules\Collector\Commands\GetGcomSingelCommand;

class CollectorServiceProvider extends ModuleServiceProvider
{
    function getNamespace()
    {
        return 'Collector\Controllers';
    }

    function getDir()
    {
        return __DIR__;
    }

    function boot()
    {
        $this->commands([
            GetCcsCommand::class,
            GetGcomsCommand::class,
            GetCustomerCommand::class,
            ReMatchTrucksCommand::class,
            GetCcsByCargoCommand::class,
            GetGcomSingelCommand::class,
            GetGcomsBijacsCommand::class,
            ImportCustomersCommand::class,
            FindDiscrepancyCommand::class,
            GetTabarInvoicesCommand::class,
            InvoiceBandarImportCommand::class,
            GetTabarInvoicesByCcsCommand::class,
            GetTabarInvoiceWithReceiptCommand::class,
        ]);

        $this->registerSchedule();

        CcsData::creating(function ($model) {
            $service = new PlateService();
            $model->vehicle_number_normal = $service->normalizePlate($model->VehicleNumber);
        });
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
