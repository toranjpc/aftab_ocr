<?php

namespace Modules\Collector\Services;

use Carbon\Carbon;
use Modules\Gcoms\Models\GcomsData;
use Illuminate\Support\Facades\Http;
use Modules\Gcoms\Models\GcomsBijac;
use Modules\Collector\Services\CustomerService;
use Modules\BijacInvoice\Models\Bijac;

class GcomsService
{
    static function invoiceRequest($invoiceSerial = 'AFTAB03-3872', $serial = '10310534')
    {
        $url = "https://core.pomix.pmo.ir/v2/soap/Invoke";

        $payload = '
                <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:mas="http://schemas.datacontract.org/2004/07/Massan.Models.Core">
                <soapenv:Header/>
                <soapenv:Body>
                    <tem:Invoke>
                        <tem:model>
                            <mas:Credential>
                                <mas:Code>aftabd</mas:Code>
                                <mas:Password>Aft@bD123</mas:Password>
                            </mas:Credential>
                            <mas:Parameters>
                                <mas:ApiServiceParameterModel>
                                    <mas:ParameterName>serial</mas:ParameterName>
                                    <mas:ParameterValue>' . $serial . '</mas:ParameterValue>
                                </mas:ApiServiceParameterModel>
                                <mas:ApiServiceParameterModel>
                                    <mas:ParameterName>InvoiceSerial</mas:ParameterName>
                                    <mas:ParameterValue>' . $invoiceSerial . '</mas:ParameterValue>
                                </mas:ApiServiceParameterModel>
                                <mas:ApiServiceParameterModel>
                                    <mas:ParameterName>portCode</mas:ParameterName>
                                    <mas:ParameterValue>10</mas:ParameterValue>
                                </mas:ApiServiceParameterModel>
                            </mas:Parameters>
                            <mas:Service>gcoms-GetInvoiceInfo</mas:Service>
                        </tem:model>
                    </tem:Invoke>
                </soapenv:Body>
                </soapenv:Envelope>
	        ';

        $http = Http::withHeaders([
            'Content-Type' => 'application/xml',
        ])
            ->withoutVerifying()
            ->withBody($payload, "text/xml")->post($url);

        return $http->body();
    }

    static function makeStandardInvoiceData($body)
    {
        $body = str_replace("\n", '', subject: $body);
        $body = str_replace("\t", '', $body);
        $body = str_replace('&lt;', '<', $body);
        $body = str_replace('&gt;', '>', $body);
        $body = str_replace('d4p1:', '', $body);
        $body = str_replace('s:', '', $body);
        $body = str_replace('a:', '', $body);
        $body = str_replace('d5p1:', '', $body);

        $body = str_replace('i:nil="true"', '', $body);
        $body = str_replace('xmlns:d5p1="http://schemas.datacontract.org/2004/07/System.Collections.Generic"', '', $body);
        try {
            $xml = new \SimpleXMLElement(html_entity_decode($body));
        } catch (\Exception $e) {
            $xml = [];
        }
        $json = json_encode($xml);
        $array = json_decode($json, true);
        $data = $array['Body']['InvokeResponse']['InvokeResult']['ResponseText']['Envelope']['Body']['GetInvoiceInfo_ByReceiptSerialAndInvoiceSerialResponse']['GetInvoiceInfo_ByReceiptSerialAndInvoiceSerialResult']['InvoiceInfo_Aftab'] ?? [];

        return collect($data)
            ->map(function ($item) {
                foreach ($item as $key => $value) {
                    if (is_array($value) && empty($value)) {
                        $item[$key] = null;
                    }
                }
                return $item;
            })
            ->toArray();
    }

    static function storeInvoiceData(&$data)
    {
        foreach ($data as $index => $item) {
            $service = new CustomerService();

            $customer = $service->import($item);

            $data[$index]['customer_id'] = $customer->id;

            try {
                GcomsData::create(
                    $item + ['customer_id' => $customer->id]
                );
            } catch (\Exception $e) {
                logger($e->getMessage());
            }
        }
    }

    public static function getInvoice($invoiceSerial = 'AFTAB03-3872', $serial = '10310534')
    {
        $cacheKey = 'gcoms_invoice_request_count' . now()->format('Y-m-d-H');

        $requestCount = cache($cacheKey, 0);

        if ($requestCount == 95)
            return 'max-request';

        $serial = (string) $serial;

        $response = static::invoiceRequest($invoiceSerial, $serial);

        $requestCount++;

        cache([$cacheKey => $requestCount], 60 * 60 * 24);

        $data = static::makeStandardInvoiceData($response);

        static::storeInvoiceData($data);

        return $data;
    }


    static function bijacByDateRequest(Carbon $date)
    {
        $url = "https://core.pomix.pmo.ir/v2/soap/Invoke";

        $payload = '
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:mas="http://schemas.datacontract.org/2004/07/Massan.Models.Core">
        <soapenv:Header/>
        <soapenv:Body>
           <tem:Invoke>
              <tem:model>
                 <mas:Credential>
                    <mas:Code>aftabd</mas:Code>
                    <mas:Password>Aft@bD123</mas:Password>
                 </mas:Credential>
                 <mas:Parameters>
                        <mas:ApiServiceParameterModel>
                            <mas:ParameterName>portCode</mas:ParameterName>
                            <mas:ParameterValue>10</mas:ParameterValue>
                        </mas:ApiServiceParameterModel>
                        <mas:ApiServiceParameterModel>
                            <mas:ParameterName>gatePassSerial</mas:ParameterName>
                            <mas:ParameterValue>0</mas:ParameterValue>
                        </mas:ApiServiceParameterModel>
                        <mas:ApiServiceParameterModel>
                            <mas:ParameterName>startDate</mas:ParameterName>
                            <mas:ParameterValue>' . $date->format('Y-m-d') . '</mas:ParameterValue>
                        </mas:ApiServiceParameterModel>
                        <mas:ApiServiceParameterModel>
                            <mas:ParameterName>endDate</mas:ParameterName>
                            <mas:ParameterValue>' . $date->format('Y-m-d') . '</mas:ParameterValue>
                        </mas:ApiServiceParameterModel>
                 </mas:Parameters>
                 <mas:Service>gcoms-GetGatePassByGTS</mas:Service>
              </tem:model>
           </tem:Invoke>
        </soapenv:Body>
     </soapenv:Envelope>
         ';

        $http = Http::withHeaders([
            'Content-Type' => 'application/xml',
        ])
            ->withoutVerifying()
            ->withBody($payload, "text/xml")->post($url);

        return $http->body();
    }

    static function makeStandardPlate($plate)
    {
        if (!str_contains($plate, 'ایران')) {
            return $plate;
        }

        $map = [
            'ع' => 'ein',
            'ج' => 'j',
            'ل' => 'l',
            'ق' => 'gh',
            'َش' => 'sh',
        ];

        $string = $plate;

        $string = str_replace('ایران', '', $string);
        $string = str_replace(' ', '', $string);
        $string = str_replace('-', ',', $string);

        foreach ($map as $key => $value) {
            $string = str_replace($key, ',' . $value . ',', $string);
        }

        $array = explode(',', $string);
        $array = array_reverse($array);
        $string = implode('', $array);

        return $string;
    }

    static function makeStandardBijacs($body)
    {
        $body = str_replace("\n", '', $body);
        $body = str_replace("\t", '', $body);
        $body = str_replace('&lt;', '<', $body);
        $body = str_replace('&gt;', '>', $body);
        $body = str_replace('d4p1:', '', $body);
        $body = str_replace('s:', '', $body);
        $body = str_replace('a:', '', $body);
        $body = str_replace('d5p1:', '', $body);

        $body = str_replace('i:nil="true"', '', $body);
        return   $body = str_replace('xmlns:d5p1="http://schemas.datacontract.org/2004/07/System.Collections.Generic"', '', $body);
        try {
            $xml = new \SimpleXMLElement(html_entity_decode($body));
        } catch (\Exception $e) {
            $xml = [];
        }
        $json = json_encode($xml);
        $array = json_decode($json, true);

        $data = $array['Body']['InvokeResponse']['InvokeResult']['ResponseText']['Envelope']['Body']['GetGatePassInfo_BydateAndGreaterThanSerialResponse']['GetGatePassInfo_BydateAndGreaterThanSerialResult']['GetGatePass_Aftab'] ?? [];

        return collect($data)
            ->map(function ($item) {
                foreach ($item as $key => $value) {
                    if ($key == 'Travel')
                        $item['Travel'] = static::makeStandardPlate($item['Travel']);

                    if (is_array($value) && empty($value)) {
                        $item[$key] = null;
                    }
                }
                return $item;
            })
            ->toArray();
    }

    static function storeBijacs($data)
    {
        foreach ($data as $item) {
            Bijac::firstOrCreate(
                $item
            );
        }
    }

    public static function getBijacByDate(Carbon $date)
    {
        $response = static::bijacByDateRequest($date);

        $data = static::makeStandardBijacs($response);

        static::storeBijacs($data);
    }

    public static function getBijacTaki($serial = '')
    {
        $url = "https://core.pomix.pmo.ir/v2/soap/Invoke";

        $payload = '
                <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:mas="http://schemas.datacontract.org/2004/07/Massan.Models.Core">
                <soapenv:Header/>
                <soapenv:Body>
                    <tem:Invoke>
                        <tem:model>
                            <mas:Credential>
                                <mas:Code>aftabd</mas:Code>
                                <mas:Password>Aft@bD123</mas:Password>
                            </mas:Credential>
                            <mas:Parameters>
                                <mas:ApiServiceParameterModel>
                                    <mas:ParameterName>portCode</mas:ParameterName>
                                    <mas:ParameterValue>10</mas:ParameterValue>
                                </mas:ApiServiceParameterModel>
                                <mas:ApiServiceParameterModel>
                                    <mas:ParameterName>gatePassSerial</mas:ParameterName>
                                    <mas:ParameterValue>' . $serial . '</mas:ParameterValue>
                                </mas:ApiServiceParameterModel>
                            </mas:Parameters>
                            <mas:Service>gcoms-GetGatePassInfo</mas:Service>
                        </tem:model>
                    </tem:Invoke>
                </soapenv:Body>
                </soapenv:Envelope>
         ';

        $http = Http::withHeaders([
            'Content-Type' => 'application/xml',
        ])
            ->withoutVerifying()
            ->withBody($payload, "text/xml")
            ->retry(3, 100)
            ->post($url);

        return  $response = $http->body();

        $data = static::makeStandardBijacs($response);

        static::storeBijacs($data);

        return $data;
    }
}
