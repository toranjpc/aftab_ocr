<?php

namespace Modules\BijacInvoice\Clients;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BijacApiClient
{
    protected $baseUrl;
    protected $token;

    public function __construct()
    {
        // $this->baseUrl = config('services.bijac.url');
        // $this->token = config('services.bijac.token');
        $this->baseUrl = env('NEW_BIJAC_SERVICE_URL', 'http://172.16.150.2:8000');
        $this->token = env('NEW_BIJAC_SERVICE_token', 'kICfPLqe6xldx2eV-2DlW-eoieA7E641oetSRhTVHEY');
    }

    public function fetchBijacs($lastBijac = 0, $lastInvoice = 0)
    {
        try {
            /*
            $response = Http::withToken($this->token)
                ->withHeaders([
                    'Accept' => 'application/json',
                ])
                ->get("{$this->baseUrl}/updated-bijacs", [
                    'since_updated' => $lastBijac,
                    'lastInvoice' => $lastInvoice
                ]);
                */
            $DATA = [];
            if ($lastBijac) $DATA["start_id_bijac"] = $lastBijac;
            if ($lastInvoice) $DATA["start_id_invoice"] = $lastInvoice;
            $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Token' => $this->token,
                ])
                ->get("{$this->baseUrl}/get_bijac_invoice", $DATA);


            return $response->json();
        } catch (\Exception $e) {
            Log::error('Failed to fetch bijacs: ' . $e->getMessage());
            return [];
        }
    }
}
