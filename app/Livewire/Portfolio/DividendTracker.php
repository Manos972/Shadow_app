<?php
namespace App\Livewire\Portfolio;

use App\Models\Dividend;
use App\Services\PortfolioOptimizationService;
use Livewire\Component;

class DividendTracker extends Component
{
    public bool $showModal = false;
    public string $symbol = '';
    public string $name = '';
    public string $portfolio_id = '';
    public string $amount_per_share = '';
    public string $shares = '';
    public string $type = 'eligible';
    public string $ex_date = '';
    public string $pay_date = '';
    public bool $is_drip = false;
    public string $drip_price = '';

    protected $rules = [
        'symbol' => 'required|string|max:20',
        'amount_per_share' => 'required|numeric|min:0.0001',
        'shares' => 'required|numeric|min:0',
        'type' => 'required|in:ordinary,eligible,return_of_capital,capital_gain',
        'ex_date' => 'required|date',
        'pay_date' => 'nullable|date',
        'drip_price' => 'nullable|numeric|min:0',
    ];

    public function openModal(?string $symbol = null): void
    {
        $this->reset(['symbol', 'name', 'amount_per_share', 'shares', 'drip_price', 'is_drip']);
        $this->symbol = $symbol ?? '';
        $this->type = 'eligible';
        $this->ex_date = now()->format('Y-m-d');
        $this->portfolio_id = (string) (auth()->user()->portfolios()->first()?->id ?? '');

        if ($symbol) {
            $position = auth()->user()->positions()->where('symbol', $symbol)->where('is_open', true)->first();
            if ($position) {
                $this->shares = $position->quantity;
                $this->name = $position->name;
            }
        }
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        Dividend::create([
            'user_id' => auth()->id(),
            'portfolio_id' => $this->portfolio_id ?: null,
            'symbol' => strtoupper($this->symbol),
            'name' => $this->name ?: null,
            'amount_per_share' => (float) $this->amount_per_share,
            'shares' => (float) $this->shares,
            'type' => $this->type,
            'ex_date' => $this->ex_date,
            'pay_date' => $this->pay_date ?: null,
            'is_drip' => $this->is_drip,
            'drip_price' => $this->is_drip && $this->drip_price ? (float) $this->drip_price : null,
            'drip_shares' => $this->is_drip && $this->drip_price ? round(((float)$this->amount_per_share * (float)$this->shares) / (float)$this->drip_price, 6) : null,
        ]);

        $this->showModal = false;
        session()->flash('success', 'Dividende enregistré.');
        $this->dispatch('dividend-saved');
    }

    public function render()
    {
        $user = auth()->user();
        $optService = app(PortfolioOptimizationService::class);

        $dividends = $user->dividends ?? Dividend::where('user_id', $user->id)->orderByDesc('ex_date')->limit(50)->get();
        $ytdTotal = Dividend::where('user_id', $user->id)->whereYear('ex_date', now()->year)->sum(\DB::raw('amount_per_share * shares'));
        $lastYearTotal = Dividend::where('user_id', $user->id)->whereYear('ex_date', now()->year - 1)->sum(\DB::raw('amount_per_share * shares'));

        $byMonth = Dividend::where('user_id', $user->id)
            ->where('ex_date', '>=', now()->subYear())
            ->get()
            ->groupBy(fn($d) => $d->ex_date->format('Y-m'))
            ->map(fn($g) => $g->sum(fn($d) => $d->totalAmount()))
            ->sortKeys();

        return view('livewire.portfolio.dividend-tracker', [
            'dividends' => Dividend::where('user_id', $user->id)->orderByDesc('ex_date')->limit(30)->get(),
            'ytdTotal' => $ytdTotal,
            'lastYearTotal' => $lastYearTotal,
            'byMonth' => $byMonth,
            'portfolios' => $user->portfolios()->get(),
            'dividendYield' => $optService->dividendYield($user),
        ]);
    }
}
