<?php

namespace App\Services;

use App\Models\MarketDataCache;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class StockDataService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 10,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function getQuote(string $symbol): array
    {
        $cacheKey = "stock_quote_{$symbol}";

        return Cache::remember($cacheKey, 900, function () use ($symbol) {
            return $this->fetchFromYahoo($symbol);
        });
    }

    public function getHistory(string $symbol, string $range = '1y', string $interval = '1d'): array
    {
        $cacheKey = "stock_history_{$symbol}_{$range}";

        return Cache::remember($cacheKey, 3600, function () use ($symbol, $range, $interval) {
            return $this->fetchHistoryFromYahoo($symbol, $range, $interval);
        });
    }

    public function search(string $query): array
    {
        $cacheKey = "stock_search_" . md5($query);

        return Cache::remember($cacheKey, 3600, function () use ($query) {
            try {
                $url = "https://query1.finance.yahoo.com/v1/finance/search?q=" . urlencode($query) . "&quotesCount=10&newsCount=0";
                $response = $this->client->get($url);
                $data = json_decode($response->getBody()->getContents(), true);

                $results = [];
                foreach ($data['quotes'] ?? [] as $quote) {
                    if (isset($quote['symbol'])) {
                        $results[] = [
                            'symbol' => $quote['symbol'],
                            'name' => $quote['longname'] ?? $quote['shortname'] ?? $quote['symbol'],
                            'exchange' => $quote['exchDisp'] ?? $quote['exchange'] ?? '',
                            'type' => $quote['quoteType'] ?? '',
                        ];
                    }
                }
                return $results;
            } catch (\Exception $e) {
                return [];
            }
        });
    }

    private function fetchFromYahoo(string $symbol): array
    {
        try {
            $url = "https://query1.finance.yahoo.com/v8/finance/chart/{$symbol}?interval=1d&range=5d";
            $response = $this->client->get($url);
            $data = json_decode($response->getBody()->getContents(), true);

            $chart = $data['chart']['result'][0] ?? null;
            if (!$chart) throw new \Exception("No data for symbol {$symbol}");

            $meta = $chart['meta'];
            $timestamps = $chart['timestamp'] ?? [];
            $closes = $chart['indicators']['quote'][0]['close'] ?? [];

            $currentPrice = $meta['regularMarketPrice'] ?? end($closes);
            $previousClose = $meta['chartPreviousClose'] ?? $meta['previousClose'] ?? null;

            // Update DB cache
            $this->updateCache($symbol, $meta, $currentPrice, $previousClose, $timestamps, $closes);

            return [
                'symbol' => $symbol,
                'name' => $meta['longName'] ?? $meta['shortName'] ?? $symbol,
                'exchange' => $meta['exchangeName'] ?? '',
                'price' => round($currentPrice, 4),
                'previous_close' => round($previousClose ?? $currentPrice, 4),
                'change' => round($currentPrice - ($previousClose ?? $currentPrice), 4),
                'change_percent' => $previousClose ? round((($currentPrice - $previousClose) / $previousClose) * 100, 2) : 0,
                'currency' => $meta['currency'] ?? 'USD',
                'market_state' => $meta['marketState'] ?? 'CLOSED',
                'volume' => $meta['regularMarketVolume'] ?? 0,
                'day_high' => $meta['regularMarketDayHigh'] ?? null,
                'day_low' => $meta['regularMarketDayLow'] ?? null,
                'week_52_high' => $meta['fiftyTwoWeekHigh'] ?? null,
                'week_52_low' => $meta['fiftyTwoWeekLow'] ?? null,
                'fetched_at' => now()->toISOString(),
            ];
        } catch (\Exception $e) {
            // Try DB cache as fallback
            $cached = MarketDataCache::where('symbol', $symbol)->first();
            if ($cached) {
                return [
                    'symbol' => $symbol,
                    'name' => $cached->name,
                    'price' => $cached->price,
                    'previous_close' => $cached->previous_close,
                    'change' => $cached->changeAmount(),
                    'change_percent' => $cached->changePercent(),
                    'fetched_at' => $cached->fetched_at?->toISOString(),
                    'stale' => true,
                ];
            }
            throw $e;
        }
    }

    private function fetchHistoryFromYahoo(string $symbol, string $range, string $interval): array
    {
        try {
            $url = "https://query1.finance.yahoo.com/v8/finance/chart/{$symbol}?interval={$interval}&range={$range}";
            $response = $this->client->get($url);
            $data = json_decode($response->getBody()->getContents(), true);

            $chart = $data['chart']['result'][0] ?? null;
            if (!$chart) return [];

            $timestamps = $chart['timestamp'] ?? [];
            $closes = $chart['indicators']['quote'][0]['close'] ?? [];
            $highs = $chart['indicators']['quote'][0]['high'] ?? [];
            $lows = $chart['indicators']['quote'][0]['low'] ?? [];
            $volumes = $chart['indicators']['quote'][0]['volume'] ?? [];

            $history = [];
            foreach ($timestamps as $i => $ts) {
                if ($closes[$i] !== null) {
                    $history[] = [
                        'date' => date('Y-m-d', $ts),
                        'close' => round($closes[$i] ?? 0, 4),
                        'high' => round($highs[$i] ?? 0, 4),
                        'low' => round($lows[$i] ?? 0, 4),
                        'volume' => $volumes[$i] ?? 0,
                    ];
                }
            }

            // Compute technical indicators
            $closePrices = array_column($history, 'close');
            return [
                'symbol' => $symbol,
                'history' => $history,
                'indicators' => [
                    'rsi_14' => $this->computeRSI($closePrices, 14),
                    'sma_20' => $this->computeSMA($closePrices, 20),
                    'sma_50' => $this->computeSMA($closePrices, 50),
                    'sma_200' => $this->computeSMA($closePrices, 200),
                    'macd' => $this->computeMACD($closePrices),
                ],
            ];
        } catch (\Exception $e) {
            return ['symbol' => $symbol, 'history' => [], 'indicators' => []];
        }
    }

    public function updatePricesForSymbols(array $symbols): void
    {
        foreach ($symbols as $symbol) {
            try {
                Cache::forget("stock_quote_{$symbol}");
                $this->getQuote($symbol);
            } catch (\Exception $e) {
                \Log::warning("Failed to update price for {$symbol}: " . $e->getMessage());
            }
        }
    }

    private function updateCache(string $symbol, array $meta, float $price, ?float $prevClose, array $timestamps, array $closes): void
    {
        $priceHistory = [];
        foreach ($timestamps as $i => $ts) {
            if (isset($closes[$i]) && $closes[$i] !== null) {
                $priceHistory[] = ['date' => date('Y-m-d', $ts), 'close' => $closes[$i]];
            }
        }

        MarketDataCache::updateOrCreate(
            ['symbol' => $symbol],
            [
                'name' => $meta['longName'] ?? $meta['shortName'] ?? $symbol,
                'exchange' => $meta['exchangeName'] ?? null,
                'price' => $price,
                'previous_close' => $prevClose,
                'volume' => $meta['regularMarketVolume'] ?? null,
                'week_52_high' => $meta['fiftyTwoWeekHigh'] ?? null,
                'week_52_low' => $meta['fiftyTwoWeekLow'] ?? null,
                'price_history' => array_slice($priceHistory, -30),
                'fetched_at' => now(),
            ]
        );
    }

    private function computeRSI(array $prices, int $period = 14): ?float
    {
        if (count($prices) < $period + 1) return null;

        $gains = $losses = [];
        for ($i = 1; $i < count($prices); $i++) {
            $change = $prices[$i] - $prices[$i - 1];
            $gains[] = max($change, 0);
            $losses[] = max(-$change, 0);
        }

        $avgGain = array_sum(array_slice($gains, 0, $period)) / $period;
        $avgLoss = array_sum(array_slice($losses, 0, $period)) / $period;

        for ($i = $period; $i < count($gains); $i++) {
            $avgGain = ($avgGain * ($period - 1) + $gains[$i]) / $period;
            $avgLoss = ($avgLoss * ($period - 1) + $losses[$i]) / $period;
        }

        if ($avgLoss == 0) return 100.0;
        $rs = $avgGain / $avgLoss;
        return round(100 - (100 / (1 + $rs)), 2);
    }

    private function computeSMA(array $prices, int $period): ?float
    {
        if (count($prices) < $period) return null;
        $slice = array_slice($prices, -$period);
        return round(array_sum($slice) / $period, 4);
    }

    private function computeEMA(array $prices, int $period): array
    {
        if (count($prices) < $period) return [];
        $k = 2 / ($period + 1);
        $ema = [array_sum(array_slice($prices, 0, $period)) / $period];

        for ($i = $period; $i < count($prices); $i++) {
            $ema[] = $prices[$i] * $k + end($ema) * (1 - $k);
        }
        return $ema;
    }

    private function computeMACD(array $prices): ?array
    {
        $ema12 = $this->computeEMA($prices, 12);
        $ema26 = $this->computeEMA($prices, 26);

        if (empty($ema12) || empty($ema26)) return null;

        $offset = count($ema12) - count($ema26);
        $macdLine = [];
        for ($i = 0; $i < count($ema26); $i++) {
            $macdLine[] = $ema12[$i + $offset] - $ema26[$i];
        }

        $signal = $this->computeEMA($macdLine, 9);
        if (empty($signal)) return null;

        return [
            'macd' => round(end($macdLine), 4),
            'signal' => round(end($signal), 4),
            'histogram' => round(end($macdLine) - end($signal), 4),
        ];
    }
}
