<?php

namespace Modules\BijacInvoice\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\BijacInvoice\Clients\BijacApiClient;
use Modules\BijacInvoice\Models\Bijac;
use Modules\BijacInvoice\Models\Customer;
use Modules\BijacInvoice\Models\Invoice;

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
        $lastSync = cache('bijacs_last_sync_time') ?:
            Bijac::max('bijac_date') ?:
            now()->subDays(5)->toDateTimeString();

        $bijacs = $this->client
            ->fetchBijacs($lastSync)['data'] ?? [];

        $invoices = collect($bijacs)->pluck('invoices')->collapse();
        // $customers = $invoices->pluck('customer')->filter()->unique('id');

        // if ($customers->count()) {
        //     $processedCustomers = $customers->map(function ($customer) {
        //         $item = [];
        //         $item['shenase_meli'] = $customer['shenase_meli'];
        //         foreach ($this->customerFields as $field) {
        //             $item[$field] = $customer[$field];
        //         }
        //         return $item;
        //     })->toArray();

        //     Customer::upsert(
        //         $processedCustomers,
        //         ['shenase_meli'],
        //         $this->customerFields
        //     );
        // }

        if ($invoices->count()) {

            $processedInvoices = $invoices->map(function ($invoice) {
                $item = [];
                if ($invoice['customer']) {
                    $updating = [];
                    foreach ($this->customerFields as $field) {
                        $updating[$field] = $invoice['customer'][$field] ?? null;
                    }

                    $customer = Customer::updateOrCreate(
                        [
                            'shenase_meli' => $invoice['customer']['shenase_meli']
                        ],
                        $updating
                    );

                    $invoice['customer_id'] = $customer->id;
                }

                $item['source_invoice_id'] = $invoice['id'];
                foreach ($this->invoiceFields as $field) {
                    $item[$field] = $invoice[$field];
                }
                $item['request_date'] = \Carbon\Carbon::parse($invoice['created_at'])
                    ->setTimezone(config('app.timezone'));
                return $item;
            })->toArray();


            Invoice::upsert(
                $processedInvoices,
                ['source_invoice_id'],
                $this->invoiceFields
            );
        }

        if (count($bijacs)) {
            $service = new PlateService();
            $processedBijacs = collect($bijacs)->map(function ($bijac) use ($service) {
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

            Bijac::upsert(
                $processedBijacs,
                ['source_bijac_id'],
                $this->bijacFields
            );

            cache()->set(
                'bijacs_last_sync_time',
                \Carbon\Carbon::parse(
                    collect($bijacs)
                        ->max('updated_at')
                )
                    ->setTimezone(config('app.timezone'))
                    ->format('Y-m-d H:i:s')
            );
        }

        // app(BijacCacheService::class)->refreshCache();
    }
}
