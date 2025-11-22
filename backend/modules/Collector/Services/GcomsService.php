<?php

namespace Modules\Collector\Services;

use Carbon\Carbon;
use Hamcrest\Core\IsTypeOf;
use Modules\Gcoms\Models\GcomsData;
use Illuminate\Support\Facades\Http;
use Modules\Gcoms\Models\GcomsBijac;
use Modules\Collector\Services\CustomerService;
use Modules\BijacInvoice\Models\Bijac;
use SimpleXMLElement;

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
            $xml = new SimpleXMLElement(html_entity_decode($body));
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
        $body = preg_replace('/(\r?\n|\r|\t)+/', '', $body);
        $externalNamespaces = [
            'soapenv' => 'http://schemas.xmlsoap.org/soap/envelope/',
            'd4p1'    => 'http://schemas.datacontract.org/2004/07/Massan.Models.Core',
            'i'       => 'http://www.w3.org/2001/XMLSchema-instance',
            'tem'     => 'http://tempuri.org/'
        ];

        $xml = simplexml_load_string($body);
        if ($xml === false) return ['error' => 'Failed to load main SOAP body string.'];

        foreach ($externalNamespaces as $prefix => $uri)
            $xml->registerXPathNamespace($prefix, $uri);

        $responseTextNode = $xml->xpath('//d4p1:ResponseText');
        if (empty($responseTextNode))
            return ['error' => 'ResponseText Node Not Found'];

        $internalXmlString = (string)$responseTextNode[0];

        $internalXml = simplexml_load_string($internalXmlString);
        if ($internalXml === false)
            return ['error' => 'Failed to parse internal XML string.'];

        $internalNamespaces = [
            's' => 'http://schemas.xmlsoap.org/soap/envelope/',
            'a' => 'http://schemas.datacontract.org/2004/07/TransporterGateWay.Models'
        ];
        foreach ($internalNamespaces as $prefix => $uri)
            $internalXml->registerXPathNamespace($prefix, $uri);

        $gatePassNodes = $internalXml->xpath('//a:GetGatePass_Aftab');
        if (empty($gatePassNodes))
            return ['error' => 'No <a:GetGatePass_Aftab> nodes found'];

        $result = [];
        foreach ($gatePassNodes as $node) {
            // $entry = [];
            foreach ($node->children('http://schemas.datacontract.org/2004/07/TransporterGateWay.Models') as $child) {
                $result[$child->getName()] = trim((string)$child);
            }
            // $result[] = $entry;
        }

        if (!isset($result['Travel'])) return [];
        $cleanPlate = preg_replace('/ایران/i', '',  $result['Travel']);
        $cleanPlate = preg_replace('/[\s\-\_]/', '', $cleanPlate);
        $cleanPlate = str_replace('ع', 'ein', $cleanPlate);
        if (preg_match('/^(\d{2})(\d{3})(ein)(\d{2})$/', $cleanPlate, $matches)) {
            // $matches[1] = کد شهر (47)
            // $matches[2] = اعداد میانی (458)
            // $matches[3] = حرف (ein)
            // $matches[4] = عدد نهایی (79)

            $codeCity     = $matches[1];
            $middleNumber = $matches[2];
            $letter       = $matches[3];
            $finalNumber  = $matches[4];

            $reorderedPlate = $finalNumber . $letter . $middleNumber . $codeCity;

            $result['Travel'] = $reorderedPlate;
        } else {
            // $result['Travel'] = "Error: Plate format did not match expected structure.";
        }
        return $result;


        $body = str_replace("\n", '', $body);
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
            $xml = new SimpleXMLElement(html_entity_decode($body));
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
        $response = $http->body();

        return $data = static::makeStandardBijacs($response);

        static::storeBijacs($data);

        return $data;
    }
}
