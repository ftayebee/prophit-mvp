<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PolymarketService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.polymarket.api_key');
        $this->baseUrl = 'https://clob.polymarket.com';
    }

    /**
     * Fetch active markets from Polymarket API
     *
     * @return array|null
     */
    public function fetchMarkets(): ?array
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
            ])->get($this->baseUrl . '/markets');

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Polymarket API returned an error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('Polymarket API request failed', ['message' => $e->getMessage()]);
        }

        return null;
    }
}
