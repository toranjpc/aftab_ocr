<?php

namespace Modules\Collector\Services;

use Modules\Collector\Models\CustomerChild;
use Modules\Collector\Models\Customer;
use Illuminate\Support\Facades\Http;

class CustomerService
{
    public $shouldGetFull = false;

    public $mapTabari = [
        'GoodsOwnerName' => 'customer_name',
        'GoodsOwnerNationalID' => 'shenase_meli',
        'GoodsOwnerPostalCode' => 'postal_code',
        'GoodsOwnerEconommicCode' => 'code_eghtesadi',
    ];

    public $mapGcoms = [
        'BuyerAddress' => 'address',
        'BuyerEconomicCode' => 'shenase_meli',
        'BuyerName' => 'customer_name',
        'BuyerNationalID' => 'code_eghtesadi',
        'BuyerPhone' => 'phone',
        'BuyerPostalCode' => 'postal_code',
        'BuyerRegNo' => 'shomare_sabt',
    ];

    public $shouldSet = [
        'address',
        'code_eghtesadi',
        'customer_name',
        'shenase_meli',
        'phone',
        'postal_code',
        'shomare_sabt',
    ];

    public function import($row)
    {
        foreach ($this->mapGcoms as $key => $item) {
            if (isset($row[$key])) {
                $row[$item] = $row[$key];
                unset($row[$key]);
            }
        }
        foreach ($this->mapTabari as $key => $item) {
            if (isset($row[$key])) {
                $row[$item] = $row[$key];
                unset($row[$key]);
            }
        }

        foreach ($this->shouldSet as $item) {
            if (!isset($row[$item])) {
                $row[$item] = null;
            }
        }

        $row['postal_code'] = (int) substr((string) $row['postal_code'], 0, 10);

        $customer = $this->getCustomer($row);

        if (gettype($customer) === 'array')
            $customer = Customer::firstOrCreate(['shenase_meli' => $customer['shenase_meli']], $customer);

        return $customer;
    }

    public function getCustomer($row)
    {
        if (!$row['shenase_meli'])
            return false;

        $row['customer_name'] = trim(str_replace('ي', 'ی', $row['customer_name']));
        // $row['type'] = trim(str_replace('ي', 'ی', $row['type'] ?? '')); // we have no type field in this moment
        $row['type'] = $this->getType($row);
        $row['shenase_meli'] = $this->nationalCodeNormalizer($row);
        $row['code_eghtesadi'] = $this->getcode_eghtesadi($row);
        $customer = Customer::where(['shenase_meli' => $row['shenase_meli']])->first();

        if (!$customer) {
            $this->shouldGetFull = true;
        }

        if ($customer && $customer->title !== $row['customer_name']) {
            CustomerChild::query()->firstOrCreate([
                'customer_id' => $customer->id,
                'title' => $row['customer_name'],
            ]);
            $row['customer_name'] = $customer->title;
        }


        if (!$customer) {
            $child = CustomerChild::where(['title' => $row['customer_name']])->first();
            if ($child) {
                $customer = $child->customer;
                $row['shenase_meli'] = $customer->shenase_meli;
            }
        }

        if (!$customer && $this->customerIsValid($row)) {
            $customer = Customer::create([
                'shenase_meli' => $row['shenase_meli'],
                'title' => $row['customer_name'],
                "code_eghtesadi" => $row['code_eghtesadi'],
                "address" => $row['address'],
                "phone" => $row['phone'],
                "postal_code" => $row['postal_code'],
                "shomare_sabt" => $row['shomare_sabt'],
                'type' => $row['type'],
                'is_safe' => false,
            ]);
        }

        if (!$customer && $row['type'] == 2) {
            $name = $this->getName($row['customer_name']);
            $data = $this->searchRasmio($name);
            $customerRasmio = $data['companies'][0] ?? false;
            if ($customerRasmio && $customerRasmio['entityId'] == $row['shenase_meli']) {
                $customer = [
                    'shenase_meli' => $customerRasmio['entityId'],
                    'title' => $row['customer_name'],
                    'type' => $row['type'],
                    "code_eghtesadi" => $row['code_eghtesadi'],
                    "address" => $row['address'],
                    "phone" => $row['phone'],
                    "postal_code" => $row['postal_code'],
                    "shomare_sabt" => $row['shomare_sabt'],
                ];
                try {
                    $customer = Customer::create($customer);
                } catch (\Exception $e) {
                }
            }

            if ($customerRasmio && $customerRasmio['titleFa'] == $row['customer_name']) {
                $customer = [
                    'shenase_meli' => $customerRasmio['entityId'],
                    'title' => $row['customer_name'],
                    'type' => $row['type'],
                    "code_eghtesadi" => $row['code_eghtesadi'],
                    "address" => $row['address'],
                    "phone" => $row['phone'],
                    "postal_code" => $row['postal_code'],
                    "shomare_sabt" => $row['shomare_sabt'],
                ];
                try {
                    $customer = Customer::create($customer);
                } catch (\Exception $e) {
                }
            }
        }

        // if ($customer) {
        //     $row['code_eghtesadi'] = $customer->code_eghtesadi;
        // } میشه ایجا کد اقتصادی درست درمون گذاشت ولی ما فیک میزاریم بره


        if (!$customer) {
            $customer = Customer::create([
                "shenase_meli" => $row['shenase_meli'],
                "title" => $row['customer_name'],
                "code_eghtesadi" => $row['code_eghtesadi'],
                "address" => $row['address'],
                "phone" => $row['phone'],
                "postal_code" => $row['postal_code'],
                "shomare_sabt" => $row['shomare_sabt'],
                'type' => $row['type'],
                'is_safe' => false,
            ]);
        }

        return $customer;
    }

    public function getType($row)
    {
        // if ($row['type'] === 'حقیقی') {
        //     return 1;
        // }

        // if ($row['type'] === 'حقوقی') {
        //     return 2;
        // }

        if (strlen($row['shenase_meli']) <= 10) {
            return 1;
        }

        if (str_contains($row['customer_name'], '\\') || str_contains($row['customer_name'], '/')) {
            return 1;
        }

        return 2;
    }

    public function customerIsValid($row)
    {
        return $row['type'] == 1 ? $this->checkCodeMeli($row['shenase_meli']) : $this->checkShenaseMeli($row['shenase_meli']);
    }

    function economicCodeNormalizer($item)
    {
        return ($item['type'] == '1') ? '11111111111111' : '11111111111';
    }

    function getcode_eghtesadi($row)
    {
        return $row['code_eghtesadi'];
        // return $row['type'] == 1 ? '11111111111' : '11111111111111';

        // if ($row['type'] == 1 && strlen($row['code_eghtesadi']) == 12) {

        // }

        // return ($item['type'] == '1') ? '11111111111111' : '11111111111';
    }

    public function nationalCodeNormalizer($row)
    {
        $code = trim($row['shenase_meli']);

        if (!$code)
            return '0000000000';

        $code = (string) $code;

        if (strlen($code) == 9) {
            $code = '0' . $code;
        }
        if (strlen($code) == 8) {
            $code = '00' . $code;
        }

        return $code;
    }

    public function getName($name)
    {
        $name = str_replace('\\', ' ', $name);
        $name = str_replace('/', ' ', $name);
        return $name;
    }

    function makePartName($fullName)
    {
        if (strpos($fullName, '\\') !== false) {
            $parts = explode('\\', $fullName);
        } elseif (strpos($fullName, '/') !== false) {
            $parts = explode('/', $fullName);
        } else {
            if (strpos($fullName, '  ') !== false) {
                $parts = explode('  ', $fullName, 2);
            } else {
                $parts = explode(' ', $fullName, 2);
            }
        }

        return $parts;
    }

    function firstnameConvert($row)
    {
        if (!(int) $row['type'] === 1) {
            return '';
        }

        $fullName = $row['customer_name'];

        $parts = $this->makePartName($fullName);

        return trim($parts[0]);
    }

    function lastnameConvert($row)
    {
        if (!(int) $row['type'] === 1) {
            return '';
        }

        $fullName = $row['customer_name'];

        $parts = $this->makePartName($fullName);

        return trim($parts[1] ?? '');
    }

    public function searchRasmio($name)
    {
        dump('searching');

        $res = Http::get('https://damavand.rasm.io/api/v3/Search/Search?textSearch=' . $name);

        $res = $res->json();

        $companies = $res['companies']['data'] ?? [];
        $people = $res['people']['data'] ?? [];

        return [
            'people' => $people,
            'companies' => $companies,
        ];
    }

    public function checkShenaseMeli($code)
    {
        if (!$code)
            return false;

        $L = strlen($code);

        if ($L < 11 || intval($code, 10) == 0) {
            return false;
        }

        if (intval(substr($code, 3, 6), 10) == 0) {
            return false;
        }

        $c = intval(substr($code, 10, 1), 10);
        $d = intval(substr($code, 9, 1), 10) + 2;
        $z = array(29, 27, 23, 19, 17);
        $s = 0;

        for ($i = 0; $i < 10; $i++) {
            $s += ($d + intval(substr($code, $i, 1), 10)) * $z[$i % 5];
        }

        $s = $s % 11;

        if ($s == 10) {
            $s = 0;
        }

        return $c == $s;
    }

    public function checkCodeMeli($meli)
    {
        if (!$meli)
            return false;

        $cDigitLast = substr($meli, strlen($meli) - 1);
        $fMeli = strval(intval($meli));

        if ((str_split($fMeli))[0] == "0" && !(8 <= strlen($fMeli) && strlen($fMeli) < 10))
            return false;

        $nineLeftDigits = substr($meli, 0, strlen($meli) - 1);

        $positionNumber = 10;
        $result = 0;

        foreach (str_split($nineLeftDigits) as $chr) {
            $digit = intval($chr);
            $result += $digit * $positionNumber;
            $positionNumber--;
        }

        $remain = $result % 11;

        $controllerNumber = $remain;

        if (2 <= $remain) {
            $controllerNumber = 11 - $remain;
        }

        return $cDigitLast == $controllerNumber;
    }
}
