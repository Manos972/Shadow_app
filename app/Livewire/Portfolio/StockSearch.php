<?php

namespace App\Livewire\Portfolio;

use App\Services\StockDataService;
use Livewire\Component;
use Livewire\Attributes\Url;

class StockSearch extends Component
{
    #[Url]
    public string $query = '';
    public array $results = [];
    public bool $loading = false;
    public ?array $selectedQuote = null;
    public ?string $selectedSymbol = null;

    public function updatedQuery(): void
    {
        if (strlen($this->query) < 2) {
            $this->results = [];
            return;
        }
        $this->loading = true;
        $this->results = app(StockDataService::class)->search($this->query);
        $this->loading = false;
    }

    public function selectSymbol(string $symbol, string $name): void
    {
        $this->selectedSymbol = $symbol;
        $this->loading = true;
        try {
            $this->selectedQuote = app(StockDataService::class)->getQuote($symbol);
            $this->selectedQuote['name'] = $name;
        } catch (\Exception $e) {
            $this->selectedQuote = ['symbol' => $symbol, 'name' => $name, 'error' => $e->getMessage()];
        }
        $this->loading = false;
        $this->results = [];
    }

    public function render()
    {
        return view('livewire.portfolio.stock-search');
    }
}
