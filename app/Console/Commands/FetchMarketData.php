<?php

namespace App\Console\Commands;

use App\Models\Position;
use App\Models\Watchlist;
use App\Services\StockDataService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FetchMarketData extends Command
{
    protected $signature = 'market:fetch {--symbols= : Comma-separated symbols to fetch}';
    protected $description = 'Fetch latest market data for all tracked symbols';

    public function __construct(private StockDataService $stockService) {
        parent::__construct();
    }

    public function handle(): int
    {
        if ($symbolsOption = $this->option('symbols')) {
            $symbols = explode(',', $symbolsOption);
        } else {
            $positionSymbols = Position::where('is_open', true)->distinct()->pluck('symbol')->toArray();
            $watchlistSymbols = Watchlist::distinct()->pluck('symbol')->toArray();
            $symbols = array_unique(array_merge($positionSymbols, $watchlistSymbols));
        }

        if (empty($symbols)) {
            $this->info('No symbols to fetch.');
            return self::SUCCESS;
        }

        $this->info('Fetching prices for ' . count($symbols) . ' symbols...');
        $bar = $this->output->createProgressBar(count($symbols));

        $updated = 0;
        foreach ($symbols as $symbol) {
            try {
                $quote = $this->stockService->getQuote($symbol);

                // Update positions with current price
                Position::where('symbol', $symbol)->where('is_open', true)->update([
                    'current_price' => $quote['price'],
                    'previous_close' => $quote['previous_close'],
                    'price_updated_at' => now(),
                ]);

                $updated++;
            } catch (\Exception $e) {
                $this->warn("Failed: {$symbol} - " . $e->getMessage());
            }
            $bar->advance();
            usleep(500000); // 0.5s delay to avoid rate limiting
        }

        $bar->finish();
        $this->newLine();
        $this->info("Updated {$updated}/" . count($symbols) . " symbols.");

        return self::SUCCESS;
    }
}
