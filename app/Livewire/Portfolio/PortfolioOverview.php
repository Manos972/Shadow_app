<?php

namespace App\Livewire\Portfolio;

use App\Models\Portfolio;
use App\Services\PortfolioAnalysisService;
use Livewire\Component;

class PortfolioOverview extends Component
{
    public ?int $selectedPortfolioId = null;

    public function mount(): void
    {
        $first = auth()->user()->portfolios()->first();
        $this->selectedPortfolioId = $first?->id;
    }

    public function render()
    {
        $user = auth()->user();
        $service = app(PortfolioAnalysisService::class);
        $portfolios = $user->portfolios()->with(['positions' => fn($q) => $q->where('is_open', true)])->get();
        $portfolio = $portfolios->find($this->selectedPortfolioId);

        return view('livewire.portfolio.portfolio-overview', [
            'portfolios' => $portfolios,
            'portfolio' => $portfolio,
            'totalValue' => $portfolio?->totalValue() ?? 0,
            'totalCost' => $portfolio?->totalCost() ?? 0,
            'unrealizedPnl' => $portfolio?->unrealizedPnl() ?? 0,
            'realizedPnl' => $portfolio?->realizedPnl() ?? 0,
            'allocation' => $service->allocationData($user),
            'topPerformers' => $service->topPerformers($user, 5),
            'worstPerformers' => $service->worstPerformers($user, 3),
        ]);
    }
}
