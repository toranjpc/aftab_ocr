<?php

namespace Modules\Collector\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Collector\Models\CcsData;
use Modules\Collector\Services\InvoiceService;
use Illuminate\Support\Facades\Validator;

class GetTabarInvoicesByCcsCommand extends Command
{
    protected $signature = 'get:ccs-missed {--created_at=}';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $validator = Validator::make([
            'created_at' => $this->option('created_at')
        ], [
            'created_at' => ['nullable', 'date']
        ]);

        if ($validator->fails()) {
            $this->error('Whoops! The given attributes are invalid.');

            collect($validator->errors()->all())
                ->each(fn($error) => $this->line($error));
            exit;
        }

        $receipts = CcsData::whereDoesntHave('tabarInvoice')
            ->when($this->option('created_at'), function ($q) {
                return $q->where('created_at', '>=', Carbon::parse($this->option('created_at')));
            })
            ->limit(500)
            ->pluck('ReceiptNumber')
            ->toArray();

        $service = new InvoiceService();

        foreach ($receipts as $receipt) {
            $data = $service->getWithReceiptNumber($receipt);
            $service->normalize($data);
        }
    }
}
