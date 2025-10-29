<?php

namespace Modules\Collector\Commands;

use Modules\Collector\Models\TabarInvoice;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Collector\Models\TabarOffline;

class FindDiscrepancyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'find:discrepancy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    function findDiffFinantial()
    {
        // return [
        //     InvoiceBandar::count(),
        //     TabarInvoice::whereBetween('PaymentDate', [
        //         new Carbon('2023-3-21'),
        //         new Carbon('2024-3-19'),
        //     ])->count(),
        //     InvoiceBandar::count() -
        //     TabarInvoice::whereBetween('PaymentDate', [
        //         new Carbon('2023-3-21'),
        //         new Carbon('2024-3-19'),
        //     ])->count()
        // ];
        $bandar = InvoiceBandar::sum('ParkingCost');

        $tabar = TabarInvoice::whereBetween('PaymentDate', [
            new Carbon('2023-3-21'),
            new Carbon('2024-3-19'),
        ])->sum('ParkingCost');

        dd([
            $bandar,
            $tabar,
            $bandar - $tabar
        ]);
    }

    function toCSV($list)
    {
        $fp = fopen(public_path('file.csv'), 'w');

        foreach ($list as $fields) {
            fputcsv($fp, $fields);
        }

        fclose($fp);
    }


    function storeExel($data)
    {
        $this->toCSV($data);
    }

    public function handle()
    {
        // dd($this->findDiffFinantial());
        // dd($this->storeExel());

        $allCount = TabarOffline::count();

        $progressBar = $this->output->createProgressBar($allCount);
        $progressBar->setOverwrite(true);

        TabarOffline::select('id', 'ReceiptNumber', 'ParkingCost', 'PaymentDate', 'GoodsOwnerName')
            ->chunk(100, function ($invoices) use ($progressBar) {
                global $ParkingCost;
                global $i;
                global $notFound;

                foreach ($invoices as $bandarInvoice) {
                    $i++;
                    $progressBar->setProgress($i);

                    $found = TabarInvoice::select('id', 'ReceiptNumber', 'ParkingCost')->where('ReceiptNumber', $bandarInvoice->ReceiptNumber)->first();
                    if (!$found) {
                        $notFound[] = $bandarInvoice->toArray();
                        $ParkingCost += $bandarInvoice->ParkingCost;
                    }
                }
            });

        global $ParkingCost;
        global $notFound;

        $this->storeExel($notFound);
        logger(PHP_EOL . 'ParkingCost => ' . $ParkingCost);
        dump(PHP_EOL . 'ParkingCost => ' . $ParkingCost);
    }
}
