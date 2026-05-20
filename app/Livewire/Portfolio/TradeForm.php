<?php

namespace App\Livewire\Portfolio;

use App\Models\Portfolio;
use App\Models\Position;
use App\Models\Trade;
use App\Services\StockDataService;
use Livewire\Component;

class TradeForm extends Component
{
    public bool $showModal = false;
    public string $type = 'buy';
    public string $portfolio_id = '';
    public string $symbol = '';
    public string $name = '';
    public string $exchange = '';
    public string $quantity = '';
    public string $price = '';
    public string $fees = '0';
    public string $executed_at = '';
    public string $notes = '';
    public bool $loadingPrice = false;
    public ?array $currentQuote = null;

    protected $rules = [
        'type' => 'required|in:buy,sell',
        'portfolio_id' => 'required|exists:portfolios,id',
        'symbol' => 'required|string|max:20',
        'quantity' => 'required|numeric|min:0.000001',
        'price' => 'required|numeric|min:0',
        'fees' => 'nullable|numeric|min:0',
        'executed_at' => 'required|date',
    ];

    public function openModal(string $type = 'buy', ?string $symbol = null): void
    {
        $this->resetValidation();
        $this->type = $type;
        $this->symbol = $symbol ?? '';
        $this->name = '';
        $this->quantity = '';
        $this->price = '';
        $this->fees = '0';
        $this->executed_at = now()->format('Y-m-d\TH:i');
        $this->currentQuote = null;
        $this->portfolio_id = (string) (auth()->user()->portfolios()->first()?->id ?? '');
        $this->showModal = true;

        if ($symbol) {
            $this->fetchPrice();
        }
    }

    public function fetchPrice(): void
    {
        if (!$this->symbol) return;
        $this->loadingPrice = true;
        try {
            $quote = app(StockDataService::class)->getQuote(strtoupper($this->symbol));
            $this->currentQuote = $quote;
            $this->price = $quote['price'];
            $this->name = $this->name ?: $quote['name'];
            $this->exchange = $this->exchange ?: $quote['exchange'];
        } catch (\Exception $e) {}
        $this->loadingPrice = false;
    }

    public function save(): void
    {
        $this->validate();

        $symbol = strtoupper(trim($this->symbol));
        $qty = (float) $this->quantity;
        $price = (float) $this->price;
        $fees = (float) $this->fees;

        $position = Position::firstOrNew([
            'portfolio_id' => $this->portfolio_id,
            'symbol' => $symbol,
        ]);

        if ($this->type === 'buy') {
            if ($position->exists) {
                $totalCost = ($position->quantity * $position->avg_buy_price) + ($qty * $price) + $fees;
                $newQty = $position->quantity + $qty;
                $position->avg_buy_price = $totalCost / $newQty;
                $position->quantity = $newQty;
            } else {
                $position->fill([
                    'user_id' => auth()->id(),
                    'name' => $this->name ?: $symbol,
                    'exchange' => $this->exchange,
                    'quantity' => $qty,
                    'avg_buy_price' => ($qty * $price + $fees) / $qty,
                    'is_open' => true,
                ]);
            }
            $position->save();

        } elseif ($this->type === 'sell') {
            if (!$position->exists || $position->quantity < $qty) {
                $this->addError('quantity', 'Quantité insuffisante en portefeuille.');
                return;
            }
            $realizedPnl = ($price - $position->avg_buy_price) * $qty - $fees;
            $position->realized_pnl += $realizedPnl;
            $position->quantity -= $qty;
            $position->is_open = $position->quantity > 0.000001;
            $position->save();
        }

        Trade::create([
            'portfolio_id' => $this->portfolio_id,
            'position_id' => $position->id,
            'user_id' => auth()->id(),
            'symbol' => $symbol,
            'type' => $this->type,
            'quantity' => $qty,
            'price' => $price,
            'fees' => $fees,
            'executed_at' => $this->executed_at,
            'notes' => $this->notes ?: null,
        ]);

        $this->showModal = false;
        session()->flash('success', ucfirst($this->type) . ' enregistré : ' . $qty . ' × ' . $symbol);
        $this->dispatch('trade-saved');
    }

    public function render()
    {
        return view('livewire.portfolio.trade-form', [
            'portfolios' => auth()->user()->portfolios()->get(),
        ]);
    }
}
