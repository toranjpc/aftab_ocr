<?php

namespace Modules\Collector\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class CcsService
{
    public function makeRequest($cargoId = 0)
    {
        if (!$cargoId) return false;
        $url = "https://core.pomix.pmo.ir/soap/Invoke";

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
                                    <mas:ParameterName>ExitPermissionNumber</mas:ParameterName>
                                    <mas:ParameterValue>' . $cargoId . '</mas:ParameterValue>
                                </mas:ApiServiceParameterModel>
                    <mas:ApiServiceParameterModel>
                                    <mas:ParameterName>Password</mas:ParameterName>
                                    <mas:ParameterValue>Add@1234</mas:ParameterValue>
                                </mas:ApiServiceParameterModel>
                    <mas:ApiServiceParameterModel>
                                    <mas:ParameterName>Username</mas:ParameterName>
                                    <mas:ParameterValue>add</mas:ParameterValue>
                                </mas:ApiServiceParameterModel>

                                </mas:Parameters>
                                <mas:Service>ccs-GetEPByNumberLimited</mas:Service>
                            </tem:model>
                        </tem:Invoke>
                    </soapenv:Body>
                    </soapenv:Envelope>
            ';

        return $http = Http::withHeaders([
            'Content-Type' => 'application/xml',
            'soapaction' => 'http://bamdadcomputer.com/IExitPermissionSVC/GetExitPermissionByDateLimited',
        ])
            ->withoutVerifying()
            ->withBody($payload, "text/xml")->post($url);

        return  $body = $http->body();
        // dd($body);
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
        $data = $array['Body']['GetExitPermissionByNumberLimitedResponse']['GetExitPermissionByNumberLimitedResult']['ExitPermissionInfoList']['ExitPermissionLimitedInfo'] ?? [];
        if (isset($data['ContainerSize'])) {
            $data = [$data];
        }
        return $data;
    }

    public function normalize(&$body)
    {

        $body = preg_replace('/(\r?\n|\r|\t)+/', '', $body);

        $externalNamespaces = [
            'soapenv' => 'http://schemas.xmlsoap.org/soap/envelope/',
            'd4p1'    => 'http://schemas.datacontract.org/2004/07/ApiGateway.Models.Core',
            'i'       => 'http://www.w3.org/2001/XMLSchema-instance',
            'tem'     => 'http://tempuri.org/'
        ];

        $xml = simplexml_load_string($body);
        if ($xml === false) return ['error' => 'Failed to load main SOAP body string.'];

        foreach ($externalNamespaces as $prefix => $uri) {
            $xml->registerXPathNamespace($prefix, $uri);
        }

        $responseTextNode = $xml->xpath('//d4p1:ResponseText');
        if (empty($responseTextNode)) {
            $isSuccessful = $xml->xpath('//d4p1:IsSuccessful');
            if (!empty($isSuccessful) && (string)$isSuccessful[0] === 'false') {
                $errorDesc = $xml->xpath('//d4p1:ErrorDescription');
                return ['error' => 'API Call Failed: ' . (string)($errorDesc[0] ?? 'No Error Description Provided')];
            }
            return ['error' => 'ResponseText Node Not Found.'];
        }

        $internalXmlString = (string)$responseTextNode[0];

        $internalXml = simplexml_load_string($internalXmlString);
        if ($internalXml === false) {
            return ['error' => 'Failed to parse internal XML string.'];
        }

        $internalNamespaces = [
            's' => 'http://schemas.xmlsoap.org/soap/envelope/',
            'a' => 'http://schemas.datacontract.org/2004/07/CCS.Common.DTO'
        ];
        foreach ($internalNamespaces as $prefix => $uri) {
            $internalXml->registerXPathNamespace($prefix, $uri);
        }

        $gatePassNodes = $internalXml->xpath('//a:ExitPermissionInfoList/a:ExitPermissionLimitedInfo');

        if (empty($gatePassNodes)) {
            return ['error' => 'No <a:ExitPermissionLimitedInfo> nodes found within ExitPermissionInfoList'];
        }

        $result = [];
        $nsA = 'http://schemas.datacontract.org/2004/07/CCS.Common.DTO';

        foreach ($gatePassNodes as $node) {
            $entry = [];
            foreach ($node->children($nsA) as $child) {
                $key = $child->getName();
                $value = trim((string)$child);
                $entry[$key] = $value;
            }
            $result[] = $entry;
        }



        return $result;





        /************************* */

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

    public function getByReceipt($cargoId)
    {
        $id = time();
        $startTime = microtime(true);

        log::info($id . " 2- start request : " . microtime(true) - $startTime);
        $data = $this->makeRequest($cargoId);

        log::info($id . " 3- request down (" . strlen($data) . "kb) : " . microtime(true) - $startTime);
        $normal = $this->normalize($data);
        log::info($id . " 4- normalize down : " . microtime(true) - $startTime);
        return $normal;
        dd($normal);
    }
}
