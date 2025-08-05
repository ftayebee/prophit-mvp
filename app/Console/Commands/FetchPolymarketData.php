<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PolymarketService;
use Illuminate\Support\Facades\Log;

class FetchPolymarketData extends Command
{
    protected $signature = 'polymarket:fetch-data';
    protected $description = 'Fetch market data from Polymarket API';

    protected $polymarketService;

    public function __construct(PolymarketService $polymarketService)
    {
        parent::__construct();
        $this->polymarketService = $polymarketService;
    }

    public function handle()
    {
        $this->info('Fetching Polymarket data...');

        try {
            $markets = $this->polymarketService->fetchMarkets();

            if (!$markets) {
                $this->error('Failed to fetch markets.');
                Log::error('Failed to fetch markets from Polymarket API: Empty or null response.');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('Exception occurred while fetching markets: ' . $e->getMessage());
            Log::error('Exception fetching markets from Polymarket API: ' . $e->getMessage(), ['exception' => $e]);
            return 1;
        }

        $minVolume = 100; // minimum volume threshold
        $significantThreshold = 10; // 10% change threshold
        $this->info('Processing ' . count($markets) . ' fetched markets...');

        foreach ($markets as $marketData) {
            $volume = $marketData['volume'] ?? 0;
            Log::info('Processing market', [
                'id' => $marketData['id'] ?? 'unknown',
                'volume' => $volume,
                'probability' => $marketData['probability'] ?? 0,
            ]);

            if ($volume < $minVolume) {
                continue; // skip low volume markets
            }

            try {
                // Update or create market record
                $market = \App\Models\Market::updateOrCreate(
                    ['market_id' => $marketData['id']],
                    [
                        'question' => $marketData['question'],
                        'volume' => $volume,
                        'current_probability' => $marketData['probability'],
                        'last_update' => now(),
                    ]
                );

                // Get last recorded probability
                $lastProb = $market->probabilities()->latest('recorded_at')->first();

                $newProb = $marketData['probability'];
                $percentChange = null;

                if ($lastProb) {
                    $oldProb = $lastProb->probability;
                    $base = max($oldProb, 0.0001);
                    $percentChange = abs($newProb - $oldProb) / $base * 100;
                }

                // Store new probability record
                \App\Models\MarketProbability::create([
                    'market_id' => $market->id,
                    'probability' => $newProb,
                    'recorded_at' => now(),
                ]);


                if ($percentChange !== null && $percentChange >= $significantThreshold) {
                    $message = sprintf(
                        'Significant move detected: Market ID %s changed by %.2f%%',
                        $market->market_id,
                        $percentChange
                    );
                    $this->info($message);
                    Log::info($message);
                    // Future: trigger notifications here
                }
            } catch (\Exception $e) {
                $errorMessage = sprintf(
                    'Exception processing market %s: %s',
                    $marketData['id'] ?? 'unknown',
                    $e->getMessage()
                );
                $this->error($errorMessage);
                Log::error($errorMessage, ['exception' => $e]);
                continue;
            }
        }

        $this->info('Finished processing markets.');
        return 0;
    }
}
