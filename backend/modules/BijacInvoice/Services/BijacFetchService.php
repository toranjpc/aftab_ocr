<?php

namespace Modules\BijacInvoice\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\BijacInvoice\Clients\BijacApiClient;
use Modules\BijacInvoice\Models\Bijac;
use Modules\BijacInvoice\Models\Customer;
use Modules\BijacInvoice\Models\Invoice;
use Illuminate\Support\Facades\Redis;

class BijacFetchService
{
    private $customerFields;
    private $invoiceFields;
    private $bijacFields;

    public function __construct(protected BijacApiClient $client)
    {
        $this->customerFields = [
            'title',
            'registrationDate',
            'status',
            'registrationTypeTitle',
            'lastCompanyNewsDate',
            'shomare_sabt',
            'code_eghtesadi',
            'lat',
            'lng',
            'postal_code',
            'phone',
            'mobile',
            'webSite',
            'email',
            'address',
            'province',
            'city',
            'socialMedias',
            'type',
            'is_safe'
        ];

        $this->invoiceFields = [
            'customer_id',
            'invoice_number',
            'receipt_number',
            'pay_date',
            'pay_trace',
            'amount',
            'weight',
            'number',
            'tax',
            'kutazh'
        ];

        $this->bijacFields = [
            'plate',
            // 'plate_normal',
            'dangerous_code',
            'receipt_number',
            'gross_weight',
            'pack_number',
            'is_single_carry',
            'container_size',
            'container_number',
            'bijac_date',
            'bijac_number',
            'vehicles_type',
            'exit_permission_iD',
            'type'
        ];
    }


    public function fetchAndStore()
    {
        // cache()->forget('bijacs_last_sync');
        // cache()->forget('invoice_last_sync');
        // cache()->forget('bijacs_last_sync_time');
        $baseUrl = env('NEW_BIJAC_SERVICE_URL', 'http://172.16.150.2:8000');
        $token = env('NEW_BIJAC_SERVICE_token', 'kICfPLqe6xldx2eV-2DlW-eoieA7E641oetSRhTVHEY');


        $path = storage_path('app/sync_state.json');
        if (file_exists($path)) {
            $content = file_get_contents($path);
            $data = json_decode($content, true);

            $limit = 30000000; //جدا کردن موردی ها
            $lastBijacId   = $data['bijac'] ?? Bijac::where('source_bijac_id', '<', $limit)->max('source_bijac_id');
            $limit = 40000000; //جدا کردن موردی ها
            $lastInvoiceId = $data['invoice'] ?? Invoice::where('source_invoice_id', '<', $limit)->max('source_invoice_id');
        }
        //  else {
        //     // $lastBijacId = cache('bijacs_last_sync') ?: 0;
        //     $lastBijacId = cache('bijacs_last_sync') ?: Bijac::where('source_bijac_id', '<', $limit)->max('source_bijac_id') ?: 0;
        //     // $lastInvoiceId = cache('invoice_last_sync') ?: 0;
        //     $lastInvoiceId = cache('invoice_last_sync') ?: Invoice::where('source_bijac_id', '<', $limit)->max('source_invoice_id') ?: 0;
        // }

        // $response = $this->client->fetchBijacs($lastBijacId, $lastInvoiceId);
        $DATA = [];
        $DATA["start_id_bijac"] = $lastBijacId;
        $DATA["start_id_invoice"] = $lastInvoiceId;
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Token' => $token,
        ])
            ->get("{$baseUrl}/get_bijac_invoice", $DATA);
        $response = $response->json();
        // $response = $response['data'] ?? [];

        $invoices = collect($response['data_invoice'] ?? []);
        $bijacs = collect($response['data_bijac'] ?? []);

        $data = [
            'bijac'   => $lastBijacId,
            'invoice' => $lastInvoiceId,
            'updated_at' => now()->toDateTimeString(),
        ];

        if ($invoices->count()) {
            $customers = collect($response['data_customer'] ?? []);
            $customersIndexed = $customers->keyBy('id');

            $processedInvoices = $invoices->map(function ($invoice) use ($customersIndexed) {
                $item = [];
                // $customer = $customers[$invoice['customer_id']] ?? null;
                $customer =  $customersIndexed->get($invoice['customer_id']) ?? null;

                if ($customer) {
                    $updating = [];
                    foreach ($this->customerFields as $field) {
                        $updating[$field] = $customer[$field] ?? null;
                    }

                    $customer = Customer::updateOrCreate(
                        ['shenase_meli' => $customer['shenase_meli']],
                        $updating
                    );

                    $invoice['customer_id'] = $customer->id;
                }

                $item['source_invoice_id'] = $invoice['id'];
                foreach ($this->invoiceFields as $field) {
                    $item[$field] = $invoice[$field];
                }
                $item['request_date'] = \Carbon\Carbon::parse($invoice['created_at'])->setTimezone(config('app.timezone'));
                return $item;
            })->toArray();

            if (!empty($processedInvoices)) {
                Invoice::upsert(
                    $processedInvoices,
                    ['source_invoice_id'],
                    $this->invoiceFields
                );

                $lastInvoiceFromApi = max(array_column($processedInvoices, 'source_invoice_id'));
                // cache()->forever('invoice_last_sync', $lastInvoiceFromApi);
                if ($lastInvoiceFromApi > $lastInvoiceId) $data['invoice'] = $lastInvoiceFromApi;
            }
        }

        if ($bijacs->count()) {

            $service = new PlateService();
            $processedBijacs = $bijacs->map(function ($bijac) use ($service) {
                $item = [];

                $item['source_bijac_id'] = $bijac['id'];
                foreach ($this->bijacFields as $field) {
                    $item[$field] = $bijac[$field];
                }
                $item['plate_normal'] =
                    // $bijac['type'] == 'ccs' ?
                    $service->normalizePlate($bijac['plate'])
                    // : $bijac['plate_normal']
                ;
                return $item;
            })->toArray();

            if (!empty($processedBijacs)) {
                Bijac::upsert(
                    $processedBijacs,
                    ['source_bijac_id'],
                    $this->bijacFields
                );

                $lastBijacFromApi = max(array_column($processedBijacs, 'source_bijac_id'));
                // cache()->forever('bijacs_last_sync', $lastBijacFromApi);
                if ($lastBijacFromApi > $lastBijacId) $data['bijac'] = $lastBijacFromApi;
            }
        }

        try {
            log::build(['driver' => 'single', 'path' => storage_path("logs/fetchBijacs.log"),])
                ->info("updated 4 bijacs fetched _ count : " . ($lastBijacFromApi ?? 0) . " _ invoice count : " . ($lastInvoiceFromApi ?? 0)); //, [$invoices]
        } catch (\Throwable $th) {
            log::build(['driver' => 'single', 'path' => storage_path("logs/fetchBijacs.log"),])
                ->info("updated 4 bijacs fetch unsuccessful  ");
            //throw $th;
        }

        $content = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        file_put_contents($path, $content, LOCK_EX);

        // \Modules\BijacInvoice\Jobs\CacheBijacsRedisJob::dispatch();
    }
}
