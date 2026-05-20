<?php

namespace App\Livewire\Portfolio;

use App\Models\Watchlist;
use App\Services\StockDataService;
use Livewire\Component;

class WatchlistManager extends Component
{
    public bool $showAddModal = false;
    public string $searchQuery = '';
    public array $searchResults = [];
    public string $targetPrice = '';
    public string $notes = '';
    public string $addingSymbol = '';
    public string $addingName = '';
    public string $addingExchange = '';
    public array $quotes = [];

    public function searchStock(): void
    {
        if (strlen($this->searchQuery) < 2) { $this->searchResults = []; return; }
        $this->searchResults = app(StockDataService::class)->search($this->searchQuery);
    }

    public function prepareAdd(string $symbol, string $name, string $exchange = ''): void
    {
        $this->addingSymbol = $symbol;
        $this->addingName = $name;
        $this->addingExchange = $exchange;
        $this->targetPrice = '';
        $this->notes = '';
        $this->searchResults = [];
        $this->showAddModal = true;
    }

    public function add(): void
    {
        $this->validate(['addingSymbol' => 'required', 'targetPrice' => 'nullable|numeric|min:0']);

        Watchlist::updateOrCreate(
            ['user_id' => auth()->id(), 'symbol' => strtoupper($this->addingSymbol)],
            ['name' => $this->addingName, 'exchange' => $this->addingExchange, 'target_price' => $this->targetPrice ?: null, 'notes' => $this->notes ?: null]
        );

        $this->showAddModal = false;
        session()->flash('success', "{$this->addingSymbol} ajouté à la watchlist.");
    }

    public function remove(int $id): void
    {
        Watchlist::where('id', $id)->where('user_id', auth()->id())->delete();
    }

    public function refreshQuotes(): void
    {
        $watchlist = auth()->user()->watchlists()->get();
        $service = app(StockDataService::class);
        foreach ($watchlist as $item) {
            try {
                $this->quotes[$item->symbol] = $service->getQuote($item->symbol);
            } catch (\Exception $e) {}
        }
    }

    public function mount(): void { $this->refreshQuotes(); }

    public function render()
    {
        return view('livewire.portfolio.watchlist-manager', [
            'watchlist' => auth()->user()->watchlists()->orderBy('symbol')->get(),
            'quotes' => $this->quotes,
        ]);
    }
}
