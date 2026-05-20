<?php

namespace App\Livewire\Portfolio;

use App\Models\Position;
use App\Models\Portfolio;
use App\Services\StockDataService;
use Livewire\Component;

class PositionList extends Component
{
    public ?int $portfolioId = null;
    public string $sortBy = 'symbol';
    public string $sortDir = 'asc';
    public bool $showClosed = false;

    public function mount(): void
    {
        $this->portfolioId = auth()->user()->portfolios()->first()?->id;
    }

    public function sort(string $field): void
    {
        $this->sortBy === $field
            ? $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc'
            : ($this->sortBy = $field) && ($this->sortDir = 'desc');
    }

    public function refreshPrices(): void
    {
        $stockService = app(StockDataService::class);
        $symbols = auth()->user()->positions()->where('is_open', true)->distinct()->pluck('symbol')->toArray();
        foreach ($symbols as $symbol) {
            try {
                $quote = $stockService->getQuote($symbol);
                Position::where('symbol', $symbol)->where('user_id', auth()->id())->update([
                    'current_price' => $quote['price'],
                    'previous_close' => $quote['previous_close'],
                    'price_updated_at' => now(),
                ]);
            } catch (\Exception $e) {}
        }
        session()->flash('success', 'Prix mis à jour.');
    }

    public function render()
    {
        $query = auth()->user()->positions()
            ->when($this->portfolioId, fn($q) => $q->where('portfolio_id', $this->portfolioId))
            ->when(!$this->showClosed, fn($q) => $q->where('is_open', true));

        $positions = $query->get()->sortBy(function ($p) {
            return match($this->sortBy) {
                'symbol' => $p->symbol,
                'value' => -$p->currentValue(),
                'pnl' => -$p->unrealizedPnl(),
                'pnl_percent' => -$p->unrealizedPnlPercent(),
                'day_change' => -$p->dayChangePercent(),
                default => $p->symbol,
            };
        })->values();

        $totalValue = $positions->sum(fn($p) => $p->currentValue());
        $totalPnl = $positions->sum(fn($p) => $p->unrealizedPnl());
        $totalDayChange = $positions->sum(fn($p) => $p->dayChange());

        return view('livewire.portfolio.position-list', [
            'positions' => $positions,
            'portfolios' => auth()->user()->portfolios()->get(),
            'totalValue' => $totalValue,
            'totalPnl' => $totalPnl,
            'totalDayChange' => $totalDayChange,
        ]);
    }
}
