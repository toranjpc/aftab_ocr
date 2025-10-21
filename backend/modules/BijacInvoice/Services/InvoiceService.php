<?php

namespace Modules\BijacInvoice\Services;

use Illuminate\Support\Facades\Http;

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
}
