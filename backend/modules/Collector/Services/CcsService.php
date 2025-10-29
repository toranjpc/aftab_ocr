<?php

namespace Modules\Collector\Services;

use Illuminate\Support\Facades\Http;

class CcsService
{
    public function makeRequest($cargoId = '14040811091')
    {
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
                                <mas:Service>ccs-GetCargoEPByNumLimit</mas:Service>
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

        $body = $http->body();
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
            $xml = new \SimpleXMLElement(html_entity_decode($body));
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

    public function normalize(&$data)
    {
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
        $data = $this->makeRequest($cargoId);

        $normal = $this->normalize($data);

        dd($normal);
    }
}
