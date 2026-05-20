<?php

namespace App\Livewire\Dashboard;

use App\Services\BudgetAnalysisService;
use App\Services\PortfolioAnalysisService;
use Livewire\Component;

class Overview extends Component
{
    public function render()
    {
        $user = auth()->user();
        $budgetService = app(BudgetAnalysisService::class);
        $portfolioService = app(PortfolioAnalysisService::class);

        return view('livewire.dashboard.overview', [
            'netWorth' => $user->totalNetWorth() + $user->totalPortfolioValue(),
            'bankBalance' => $user->totalNetWorth(),
            'portfolioValue' => $user->totalPortfolioValue(),
            'monthlyIncome' => $budgetService->monthlyIncome($user),
            'monthlyExpenses' => $budgetService->monthlyExpenses($user),
            'monthlySavings' => $budgetService->monthlySavings($user),
            'portfolioPnl' => $portfolioService->totalUnrealizedPnl($user),
            'portfolioDayChange' => $portfolioService->totalDayChange($user),
            'topPerformers' => $portfolioService->topPerformers($user, 3),
            'allocation' => $portfolioService->allocationData($user),
            'recentTransactions' => $user->transactions()->with(['account', 'category'])->orderByDesc('date')->limit(5)->get(),
            'budgetAdherence' => $budgetService->budgetAdherence($user),
            'monthlyChart' => $budgetService->monthlyChartData($user, 6),
        ]);
    }
}
