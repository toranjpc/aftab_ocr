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
        $this->baseUrl = config('services.bijac.url');
        $this->token = config('services.bijac.token');
    }

    public function fetchBijacs($lastSync)
    {
        try {
            $response = Http::withToken($this->token)
                ->withHeaders([
                    'Accept' => 'application/json',
                ])
                ->get("{$this->baseUrl}/updated-bijacs", [
                    'since_updated' => $lastSync
                ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Failed to fetch bijacs: ' . $e->getMessage());
            return [];
        }
    }
}
