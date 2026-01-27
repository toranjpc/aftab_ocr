<?php

use Modules\BijacInvoice\Jobs\SyncBijacsJob;
use Modules\BijacInvoice\Jobs\CacheBijacsRedisJob;

$schedule->job(new SyncBijacsJob)->everyFiveMinutes();
// $schedule->job(new SyncBijacsJob)->everyMinute();

// $schedule->job(new CacheBijacsRedisJob)->everyFiveMinutes()->withoutOverlapping();
// $schedule->job(new CacheBijacsRedisJob)->everyMinute();
