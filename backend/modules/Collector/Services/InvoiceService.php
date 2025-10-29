<?php

namespace Modules\Collector\Services;

use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Support\Facades\Http;
use Modules\Collector\Models\TabarInvoice;
use Modules\Collector\Models\TabarInvoiceContainer;

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
            return Http::withHeaders([
                'Authorization' => $jwt,
                'Content-Type' => 'application/json',
                'debug' => true
            ]);
        }

        $res = Http::post($url, $body);

        $body = $res->json('responseText');

        $jwt = json_decode($body, true)['Token'];

        dump('loggedIn');

        cache(['jwt' => $jwt]);

        return Http::withHeaders([
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

    function makeRequestByDate($http, $date, $lastId)
    {
        dump('lastID => ' . $lastId);

        $date = verta($date)->format('Y/m/d');

        $jwt = cache('jwt');

        $curl = curl_init();

        $params = "{\n    \"credential\": {\n        \"code\": \"aftabd\",\n        \"password\": \"Aft@bD123\"\n    },\n    \"parameters\": [\n        {\n            \"parameterName\": \"Username\",\n            \"parameterValue\": \"userSPAD\"\n        },\n        {\n            \"parameterName\": \"Password\",\n            \"parameterValue\": \"U*sp@d}1402\"\n        },\n        {\n            \"parameterName\": \"StartDate\",\n            \"parameterValue\": \"{$date}\"\n        },\n        {\n            \"parameterName\": \"EndDate\",\n            \"parameterValue\": \"{$date}\"\n        },\n        {\n            \"parameterName\": \"LastRecivedInvoiceID\",\n            \"parameterValue\": \"{$lastId}\"\n        },\n        {\n            \"parameterName\": \"CountRowsPerRequest\",\n            \"parameterValue\": \"{$this->itemPerRequest}\"\n        }\n    ],\n    \"service\": \"bsr-GetExitGateByInvDate\"\n}";

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://core.pomix.pmo.ir/api",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_HTTPHEADER => [
                "Authorization: " . $jwt,
                "Content-Type: application/json"
            ],
        ]);

        $response = curl_exec($curl);

        $err = curl_error($curl);

        curl_close($curl);

        $body = json_decode($response, true);

        if(!$body) return [];

        if ($body['responseText'] == '"خطا در اطلاعات توکن کاربر"') {
            cache()->forget('jwt');
            dump('reLogin');
            return 'reLogin';
        }

        if ($body['responseText'] == '"آی پی سرویس گیرنده نامعتبر است"') {
            dump('مشکل آیپی');
            return [];
        }

        if ($body['responseStatusCode'] == 400) {
            dump('failed');
            return [];
        }

        return json_decode($body['responseText'], true);
    }

    public function convertor($data1)
    {
        $miladiDate = null;
        if ($data1 && !empty(trim($data1))) {
            $jalaliDate = Verta::parse($data1);
            $miladiDate = $jalaliDate->formatGregorian('Y-m-d H:i:s');
        }
        return $miladiDate;
    }

    function normalize($response)
    {
        $data = $response['Invoices'] ?? $response;

        foreach ($data as $key => $value) {
            $value['InvoiceDate'] = $this->convertor($value['InvoiceDate']);
            $value['CalculationDate'] = $this->convertor($value['CalculationDate']);
            $value['DischargeDate'] = $this->convertor($value['DischargeDate']);
            $value['PaymentDate'] = $this->convertor($value['PaymentDate']);
            $value['AccountTitle'] = $value['Account']['AccountTitle'];
            $value['BankName'] = $value['Account']['BankName'];
            $value['BranchName'] = $value['Account']['BranchName'];
            $value['AccountNumber'] = $value['Account']['AccountNumber'];
            $value['ShabaNo'] = $value['Account']['ShabaNo'];
            $value['SellerName'] = $value['Account']['AccountOwner']['SellerName'];
            $value['TaxAccountTitle'] = $value['TaxAccount']['AccountTitle'];
            $value['TaxAccountShabaNo'] = $value['TaxAccount']['AccountTitle'];

            if (empty(trim($value['PimacsID']))) {
                $value['PimacsID'] = null;
            }

            $service = new CustomerService();

            $customer = $service->import($value);

            try {
                $TabarInvoice = TabarInvoice::firstOrCreate(
                    [
                        'InvoiceID' => $value['InvoiceID'],
                        'ReceiptNumber' => $value['ReceiptNumber'],
                    ],
                    $value + ['customer_id' => $customer->id]
                );
            } catch (\Exception $e) {
                logger($e->getMessage());
            }

            if (count($value['ContainersList']) !== 0)
                foreach ($value['ContainersList'] as $key => $Container) {
                    $Container['ExitDate'] = $this->convertor($Container['ExitDate']);
                    $Container['tabar_invoice_id'] = $TabarInvoice['id'];
                    try {
                        TabarInvoiceContainer::create($Container);
                    } catch (\Exception $e) {
                    }
                }
        }
    }

    function getByDate($date, $lastId)
    {
        $http = $this->login();

        $data = $this->makeRequestByDate($http, $date, $lastId);

        if ($data === 'reLogin') {
            return $this->getByDate($date, $lastId);
        }

        return $data;
    }
}
