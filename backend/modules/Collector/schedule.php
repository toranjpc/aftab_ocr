<?php

use Modules\Collector\Commands\ExampleCommand;
use Modules\Collector\Commands\GetCcsCommand;
use Modules\Collector\Commands\GetGcomsBijacsCommand;
use Modules\Collector\Commands\GetGcomsCommand;
use Modules\Collector\Commands\GetTabarInvoicesByCcsCommand;
use Modules\Collector\Commands\GetTabarInvoicesCommand;

$schedule->command(GetCcsCommand::class)
    ->everyTenMinutes()
    ->withoutOverlapping();

$schedule->command(GetGcomsBijacsCommand::class)
    ->everyTenMinutes()
    ->withoutOverlapping();

$schedule->command(GetGcomsCommand::class)
    ->everyThirtyMinutes()
    ->withoutOverlapping();

$schedule->command(GetTabarInvoicesByCcsCommand::class)
    ->everyTwoHours()
    ->withoutOverlapping();

$schedule->command(GetTabarInvoicesCommand::class)
    ->everyTenMinutes()
    ->withoutOverlapping();
