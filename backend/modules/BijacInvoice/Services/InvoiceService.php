<?php

namespace Modules\BijacInvoice\Services;

use Illuminate\Support\Facades\Http;
use Modules\BijacInvoice\Models\Invoice;
use Modules\BijacInvoice\Models\Customer;
use Hekmatinasser\Verta\Facades\Verta;
use Modules\Auth\Controllers\AuthController;

class InvoiceService
{
    protected $itemPerRequest;

    function __construct($itemPerRequest = 100)
    {
        $this->itemPerRequest = $itemPerRequest;
    }

    function login()
    {
        $url = 'https://core.pomix.pmo.ir/api';

        $body = [
            "credential" => [
                "code" => "aftabd",
                "password" => "Aft@bD123"
            ],
            "parameters" => [
                [
                    "parameterName" => "Username",
                    "parameterValue" => "userSPAD",
                ],
                [
                    "parameterName" => "Password",
                    "parameterValue" => "U*sp@d]1402",
                ]
            ],
            "service" => "bsr-login"
        ];

        $jwt = cache('jwt');

        if ($jwt) {
            return Http::withoutVerifying()->withHeaders([
                'Authorization' => $jwt,
                'Content-Type' => 'application/json',
                'debug' => true
            ]);
        }

        $res = Http::withoutVerifying()->post($url, $body);

        $body = $res->json('responseText');

        $jwt = json_decode($body, true)['Token'];

        // dump('loggedIn');

        cache(['jwt' => $jwt]);

        return Http::withoutVerifying()->withHeaders([
            'Authorization' => $jwt,
            'Content-Type' => 'application/json',
            'debug' => true
        ]);
    }

    function makeRequestWithReceiptNumber($http, $receiptNumber)
    {
        $url = "https://core.pomix.pmo.ir/api";

        $body = [
            "credential" => [
                "code" => "aftabd",
                "password" => "Aft@bD123"
            ],
            "parameters" => [
                [
                    "parameterName" => "WarehouseReceiptNumber",
                    "parameterValue" => $receiptNumber,
                ],
            ],
            "service" => "bsr-GetParkingCostInvoic"
        ];

        $response = $http->post($url, $body);

        $body = $response->json();

        if ($body === null) {
            sleep(2);
            return $this->makeRequestWithReceiptNumber($http, $receiptNumber);
        }

        if ($body['responseText'] == '"صورتحساب پاركينگ مورد نظر یافت نشد"') {
            return false;
        }

        if ($body['responseText'] == '"خطا در اطلاعات توکن کاربر"') {
            cache()->forget('jwt');
            $http = $this->login();
            return $this->makeRequestWithReceiptNumber($http, $receiptNumber);
        }

        return json_decode($body['responseText'], true);
    }

    function getWithReceiptNumber($receiptNumber)
    {
        $http = $this->login();

        $data = $this->makeRequestWithReceiptNumber($http, $receiptNumber);

        if ($data == false) {
            return [];
        }

        $data = collect($data)
            ->map(function ($item) {
                foreach ($item as $key => $value) {
                    if (is_array($value) && empty($value)) {
                        $item[$key] = null;
                    }
                }
                return $item;
            })
            ->toArray();

        return $data;
    }

    function save_invoice($invoiceService)
    {
        if ($invoiceService && !isset($invoiceService['InvoiceNumber'])) return;

        $customerUpdating = [];
        $customerFields = [
            "title" => "GoodsOwnerName",
            "shenase_meli" => "GoodsOwnerNationalID",
            "postal_code" => "GoodsOwnerPostalCode",
            // "shenase_meli" => "GoodsOwnerEconommicCode",
        ];
        foreach ($customerFields as $key => $field) {
            $customerUpdating[$key] = $invoiceService[$field] ?? null;
        }
        $customer = Customer::updateOrCreate(
            ['shenase_meli' => $invoiceService["GoodsOwnerNationalID"]],
            $customerUpdating
        );

        $fildes = [];
        $fildes["source_invoice_id"] = time();
        $fildes['customer_id'] = $customer->id;
        $fildes["invoice_number"] = $invoiceService['InvoiceNumber'];
        $fildes["receipt_number"] = $invoiceService['ReceiptNumber'];
        $fildes["pay_date"] =  $this->MiladiConvertor($invoiceService['InvoiceDate']);
        $fildes["pay_trace"] = $invoiceService['PayRequestTraceNo'];
        $fildes["amount"] = $invoiceService['ParkingCost'];
        $fildes["weight"] = $invoiceService['Weight'];
        $fildes["tax"] = $invoiceService['Total'] - $invoiceService['ParkingCost'];
        $fildes["kutazh"] = null;
        $fildes["number"] = null;
        $fildes["request_date"] = now();

        $Invoice = Invoice::create($fildes);
        $AuthController = new AuthController();
        $AuthController->savelog($Invoice);
    }

    function MiladiConvertor($data1)
    {
        $miladiDate = null;
        if ($data1 && !empty(trim($data1))) {
            $jalaliDate = Verta::parse($data1);
            $miladiDate = $jalaliDate->formatGregorian('Y-m-d H:i:s');
        }
        return $miladiDate;
    }
}
