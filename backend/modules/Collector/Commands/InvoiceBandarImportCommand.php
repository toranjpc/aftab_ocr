<?php

namespace Modules\Collector\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Modian\Imports\InvoiceImport;

class InvoiceBandarImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:bandar-invoice';

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
    public function handle()
    {
        $files = array_diff(scandir(public_path('files')), array('.', '..'));

        foreach ($files as $file) {
            if (str_ends_with($file, '.xlsx') || str_ends_with($file, '.xls')) {
                $path = public_path('files/' . $file);
                Excel::import(new InvoiceImport(), ($path));
                unlink($path);
            }
        }
    }
}
