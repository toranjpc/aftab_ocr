<?php

namespace Modules\Collector\Commands;

use Modules\Collector\Models\CcsData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;

class GetCcsCommand extends Command
{
    protected $signature = 'get:ccs';

    protected $description = 'get ccs data';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $lastDateCCS = cache('lastDateCCS');

        $lastDateCCS = $lastDateCCS ? (new \Carbon\Carbon($lastDateCCS)) : now()->subDays(2);

        $until = clone $lastDateCCS;

        $until->addMinutes(60 * 12);

        if (now()->addDay()->lessThan($until)) {
            dump([
                '***********',
                implode('T', explode(' ', $lastDateCCS->toDateTimeString())),
                implode('T', explode(' ', $until->toDateTimeString()))
            ]);
            cache(['lastDateCCS' => $lastDateCCS->subMinutes(60 * 24 * 3)]);
            return;
        }

        dump([
            implode('T', explode(' ', $lastDateCCS->toDateTimeString())),
            implode('T', explode(' ', $until->toDateTimeString()))
        ]);

        $data = $this->makeRequest($lastDateCCS, $until);

        dump(count($data));
		
		if(count($data)) {
			$data = collect($data)
				->map(function ($item) use ($lastDateCCS) {
					foreach ($item as $key => $value) {
						if (is_array($value) && empty($value)) {
							$item[$key] = null;
						}
					}
					$item['request_date'] = $lastDateCCS;
					return $item;
				})
				->toArray();

			$created_at = now();

			foreach ($data as $item) {

				try {
					CcsData::updateOrCreate(
						[
							'ReceiptNumber' => $item['ReceiptNumber'],
							'VehicleNumber' => $item['VehicleNumber'],
							'ExitPermissionNumber' => $item['ExitPermissionNumber']
						],
						$item
					);
				} catch (\Exception $e) {
					dump($e->getMessage());
				}
			}

			Artisan::call('get:ccs-missed', [
				'--created_at' => $created_at->toDateTimeString()
			]);
		}

        cache(['lastDateCCS' => $until->timestamp]);

        if ($until->diffInHours(now()) > 4) {
            $this->handle();
        }
    }

    public function makeRequest($lastDateCCS, $until)
    {
        $url = "https://core.pomix.pmo.ir/api/proxy/ccs-ExitPerByDateLimite/aftabd/Aft@bD123";

        $payload = '
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:bam="http://bamdadcomputer.com" xmlns:ccs="http://schemas.datacontract.org/2004/07/CCS.Common.DTO">
        <soapenv:Header/>
        <soapenv:Body>
           <bam:GetExitPermissionByDateLimited>
              <!--Optional:-->
              <bam:request>
                 <ccs:ClientCredential>
                    <ccs:Password>Add@1234</ccs:Password>
                    <ccs:Username>add</ccs:Username>
                 </ccs:ClientCredential>
                 <!--Optional:-->
                 <ccs:ExitID>0</ccs:ExitID>
                 <ccs:FromDate>' . implode('T', explode(' ', $lastDateCCS->toDateTimeString())) . '</ccs:FromDate>
                 <ccs:ToDate>' . implode('T', explode(' ', $until->toDateTimeString())) . '</ccs:ToDate>
              </bam:request>
           </bam:GetExitPermissionByDateLimited>
        </soapenv:Body>
     </soapenv:Envelope>
	';

        $http = Http::withHeaders([
            'Content-Type' => 'application/xml',
            'soapaction' => 'http://bamdadcomputer.com/IExitPermissionSVC/GetExitPermissionByDateLimited'
        ])->withBody($payload, "text/xml")->post($url);

        $body = $http->body();

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
        $data = $array['Body']['GetExitPermissionByDateLimitedResponse']['GetExitPermissionByDateLimitedResult']['ExitPermissionInfoList']['ExitPermissionLimitedInfo'] ?? [];
        if (isset($data['ContainerSize'])) {
            $data = [$data];
        }

        return $data;
    }
}
