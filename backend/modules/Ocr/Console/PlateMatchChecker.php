<?php

namespace Modules\Ocr\Console;

use Modules\Ocr\Models\OcrLog;
use Illuminate\Console\Command;

class PlateMatchChecker extends Command
{
    protected $signature = 'plate-checker';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $logs = OcrLog::with('parent')
            ->whereHas('parent', fn($q) => $q->whereHas('ccsData'))
            ->limit(1200)
            ->get();

        $true = 0;
        $false = 0;
        $i = 0;
        foreach ($logs as $log) {
            // dump($i++);
            if (!$log->tabar_invoice)
                continue;

            if ($log->tabar_invoice->id === $log->parent->tabar_invoice->id) {
                $true++;
            } else {
                dump('*********************');
                dump($log->only([
                    'plate_number',
                ]));
                dump($log->parent->ccs_has_tabar_invoice->only([
                    'VehicleNumber',
                    'ReceiptNumber',
                    'request_date',
                    'ContainerNumber',
                ]));
                dump($log->ccs_has_tabar_invoice->only([
                    'VehicleNumber',
                    'ReceiptNumber',
                    'request_date',
                    'ContainerNumber',
                ]));
                dump('*********************');
                $false++;
            }
        }

        dd([
            'true' => $true,
            'false' => $false
        ]);
    }
}
