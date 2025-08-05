<?php

use App\Services\PolymarketService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;


Route::get('/test-polymarket', function (PolymarketService $service) {
    try{
        $markets = $service->fetchMarkets();
        if ($markets) {
            Log::info('Fetched markets from Polymarket', ['count' => count($markets)]);
            return response()->json([
                'success' => true,
                'markets' => $markets,
            ]);
        }
    } catch (\Exception $e) {
        Log::error('Error fetching markets from Polymarket', ['message' => $e->getMessage()]);
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch markets from Polymarket API',
        ], 500);
    }
});
