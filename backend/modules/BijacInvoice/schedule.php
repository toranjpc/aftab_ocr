<?php

use Modules\BijacInvoice\Jobs\SyncBijacsJob;

// $schedule->job(new SyncBijacsJob)->everyFiveMinutes();
$schedule->job(new SyncBijacsJob)->everyMinute();
