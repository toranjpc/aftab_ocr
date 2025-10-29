<?php

namespace Modules\Collector\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class GetFullCustomerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $customer;

    public function __construct($customer)
    {
        $this->customer = $customer;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $customer = $this->findRasmio($this->customer->shenase_meli);
        dump($customer['shenase_meli']);
        if ($customer['shenase_meli'] == $this->customer->shenase_meli)
            $this->customer->update($customer);
    }


    public function findRasmio($id)
    {
        dump('finding');

        $res = Http::get('https://gw.rasmio.ir/api/v3/Companies/GetDetails?companyId=' . $id . '&title=Direct');

        if ($res->status() == 400) {
            sleep(60);
            return $this->findRasmio($id);
        }

        $data = $res->json();

        $data = $data['summary']['companySummary'] ?? '';

        return $this->convertToCustomer($data);
    }

    public function convertToCustomer($data)
    {
        return [
            "shenase_meli" => data_get($data, 'id'),
            "title" => data_get($data, 'title'),
            "registrationDate" => data_get($data, 'summary.registrationDate'),
            "status" => data_get($data, 'summary.status'),
            "registrationTypeTitle" => data_get($data, 'summary.registrationTypeTitle'),
            "lastCompanyNewsDate" => data_get($data, 'summary.lastCompanyNewsDate'),
            "shomare_sabt" => data_get($data, 'summary.registrationNo'),
            "code_eghtesadi" => data_get($data, 'summary.code_eghtesadi'),
            "lat" => data_get($data, 'communications.lat'),
            "lng" => data_get($data, 'communications.lng'),
            "address" => data_get($data, 'communications.address'),
            "postal_code" => data_get($data, 'communications.postalCode'),
            "phone" => data_get($data, 'communications.tel'),
            "fax" => data_get($data, 'communications.fax'),
            "mobile" => data_get($data, 'communications.mobile'),
            "webSite" => data_get($data, 'communications.webSite'),
            "email" => data_get($data, 'communications.email'),
        ];
    }
}
